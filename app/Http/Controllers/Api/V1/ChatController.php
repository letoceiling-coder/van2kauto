<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadMessage;
use App\Services\ChatService;
use App\Support\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function start(Request $request): JsonResponse
    {
        if (! $this->originAllowed($request)) {
            return response()->json(['message' => 'Origin not allowed'], 403);
        }
        if (filled($request->input('website'))) {
            return response()->json(['token' => null, 'messages' => []], 201);
        }
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
            'name' => ['nullable', 'string', 'max:120'],
            'contact' => ['nullable', 'string', 'max:120', function ($attr, $value, $fail) {
                $v = trim((string) $value);
                if ($v !== '' && ! str_starts_with($v, '@') && strlen(preg_replace('/\D/', '', $v)) < 10) {
                    $fail('Укажите телефон или @username в Telegram');
                }
            }],
            'car_slug' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'string', 'max:1024'],
            'utm' => ['nullable', 'array'],
            'utm.*' => ['nullable', 'string', 'max:255'],
            'ym_client_id' => ['nullable', 'string', 'max:64'],
        ]);
        $text = trim($data['message']);
        unset($data['message']);
        $data['utm'] = array_intersect_key($data['utm'] ?? [], array_flip(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'referrer']));
        $lead = $this->chat->start($data + ['channel' => 'Чат на сайте', 'ip' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 500)], $text);

        return response()->json(['token' => $lead->chat_token, 'messages' => $this->messages($lead)], 201);
    }

    public function send(Request $request, string $token): JsonResponse
    {
        $lead = $this->lead($token);
        $data = $request->validate([
            'message' => ['nullable', 'string', 'min:1', 'max:2000', 'required_without:contact'],
            'contact' => ['nullable', 'string', 'max:120', function ($attr, $value, $fail) {
                $v = trim((string) $value);
                if ($v !== '' && ! str_starts_with($v, '@') && strlen(preg_replace('/\D/', '', $v)) < 10) {
                    $fail('Укажите телефон или @username в Telegram');
                }
            }],
            'name' => ['nullable', 'string', 'max:120'],
        ]);
        if (! empty($data['contact'])) {
            $this->chat->updateContact($lead, $data['name'] ?? null, trim($data['contact']));
        }
        if (! empty($data['message'])) {
            $this->chat->clientMessage($lead, trim($data['message']));
        }

        return response()->json(['messages' => $this->messages($lead->fresh(), (int) $request->input('after', 0))]);
    }

    public function poll(Request $request, string $token): JsonResponse
    {
        $lead = $this->lead($token);

        return response()->json([
            'messages' => $this->messages($lead, (int) $request->input('after', 0)),
            'has_contact' => $lead->contact !== 'чат на сайте',
        ], 200, ['Cache-Control' => 'no-store']);
    }

    private function lead(string $token): Lead
    {
        abort_unless(strlen($token) === 40, 404);

        return Lead::query()->where('chat_token', $token)->firstOrFail();
    }

    /** @return list<array<string, mixed>> */
    private function messages(Lead $lead, int $after = 0): array
    {
        return $lead->messages()->where('id', '>', $after)->limit(200)->get()->map(fn (LeadMessage $m) => $m->toChat())->all();
    }

    private function originAllowed(Request $request): bool
    {
        $origin = $request->headers->get('Origin');
        if (! $origin) {
            return true;
        }
        $allowed = array_filter(array_map('trim', explode(',', (string) Settings::get('leads_allowed_origins', ''))));

        return $allowed === [] || in_array('*', $allowed, true) || in_array(rtrim($origin, '/'), array_map(fn ($o) => rtrim($o, '/'), $allowed), true);
    }
}
