<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LeadService;
use App\Support\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(private readonly LeadService $leads) {}

    public function store(Request $request): JsonResponse
    {
        if (! $this->originAllowed($request)) {
            return response()->json(['message' => 'Origin not allowed'], 403);
        }

        // Honeypot: боты заполняют скрытое поле — отвечаем «ок», но ничего не создаём.
        if (filled($request->input('website'))) {
            return response()->json(['ok' => true], 201);
        }

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'contact' => ['required', 'string', 'max:120', function ($attr, $value, $fail) {
                $value = trim((string) $value);
                if (! str_starts_with($value, '@') && strlen(preg_replace('/\D/', '', $value)) < 10) {
                    $fail('Укажите телефон или @username в Telegram');
                }
            }],
            'channel' => ['nullable', 'string', 'max:32'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'car_slug' => ['nullable', 'string', 'max:255'],
            'car_title' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'string', 'max:1024'],
            'context' => ['nullable', 'array', 'max:30'],
            'context.*' => ['nullable'],
            'utm' => ['nullable', 'array'],
            'utm.*' => ['nullable', 'string', 'max:255'],
            'ym_client_id' => ['nullable', 'string', 'max:64'],
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => 'Нужно согласие на обработку персональных данных',
        ]);

        unset($data['consent']);
        $data['context'] = $this->flatContext($data['context'] ?? []);
        $data['utm'] = array_intersect_key($data['utm'] ?? [], array_flip(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'referrer']));

        $lead = $this->leads->create($data + [
            'source' => 'site',
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        return response()->json(['ok' => true, 'id' => $lead->id], 201);
    }

    private function originAllowed(Request $request): bool
    {
        $origin = $request->headers->get('Origin');
        if (! $origin) {
            return true; // серверные запросы и curl без Origin
        }
        $allowed = array_filter(array_map('trim', explode(',', (string) Settings::get('leads_allowed_origins', ''))));

        return $allowed === [] || in_array('*', $allowed, true) || in_array(rtrim($origin, '/'), array_map(fn ($o) => rtrim($o, '/'), $allowed), true);
    }

    /** @return array<string, string> */
    private function flatContext(array $context): array
    {
        $out = [];
        foreach ($context as $key => $value) {
            $out[substr((string) $key, 0, 60)] = substr(is_array($value) ? implode(', ', array_map('strval', $value)) : (string) $value, 0, 500);
        }

        return $out;
    }
}
