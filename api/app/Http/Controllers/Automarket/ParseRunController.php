<?php

namespace App\Http\Controllers\Automarket;

use App\Http\Controllers\Controller;
use App\Models\AuctionRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class ParseRunController extends Controller
{
    public function start(Request $request): RedirectResponse
    {
        $mode = $request->input('mode');
        if (! in_array($mode, ['new', 'full'], true)) {
            return back()->withErrors(['mode' => 'Неизвестный режим']);
        }
        if (AuctionRun::query()->whereIn('status', ['pending', 'running'])->exists()) {
            return back()->withErrors(['run' => 'Уже выполняется другой парсинг']);
        }

        if ($request->boolean('resume')) {
            $run = AuctionRun::query()->whereIn('status', ['needs_captcha', 'needs_auth'])->latest('id')->first();
            if (! $run) {
                return back()->withErrors(['run' => 'Нет остановленного парсинга']);
            }
            $run->update(['status' => 'pending', 'needs_captcha' => false, 'needs_auth' => false, 'error_message' => null]);
        } else {
            $run = AuctionRun::query()->create(['mode' => $mode, 'status' => 'pending']);
        }

        $this->dispatch($run);

        return redirect()->route('automarket.dashboard')->with('run_id', $run->id);
    }

    public function show(AuctionRun $run): JsonResponse
    {
        return response()->json($run->fresh());
    }

    public function active(): JsonResponse
    {
        $run = AuctionRun::query()
            ->whereIn('status', ['pending', 'running', 'needs_captcha', 'needs_auth'])
            ->latest('id')
            ->first();

        return response()->json(['run' => $run]);
    }

    private function dispatch(AuctionRun $run): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            Process::path(base_path())->start(['php', 'artisan', 'automarket:parse-run', (string) $run->id]);

            return;
        }

        exec('cd '.base_path().' && php artisan automarket:parse-run '.$run->id.' > /dev/null 2>&1 &');
    }
}
