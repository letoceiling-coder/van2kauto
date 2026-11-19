<?php

namespace App\Http\Controllers\Che168;

use App\Http\Controllers\Controller;
use App\Models\ScrapeRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class ParseRunController extends Controller
{
    public function start(Request $request): RedirectResponse|JsonResponse
    {
        $mode = $request->input('mode');
        if (! in_array($mode, ['new', 'full'], true)) {
            return back()->withErrors(['mode' => 'invalid']);
        }

        if (ScrapeRun::query()->whereIn('status', ['pending', 'running'])->exists()) {
            return back()->withErrors(['run' => 'Уже выполняется другой парсинг']);
        }

        if ($request->boolean('resume')) {
            $run = ScrapeRun::query()->where('status', 'needs_captcha')->latest('id')->first();
            if (! $run) {
                return back()->withErrors(['run' => 'Нет остановленного парсинга']);
            }
            $run->update(['status' => 'pending', 'needs_captcha' => false, 'error_message' => null]);
        } else {
            $run = ScrapeRun::query()->create([
                'mode' => $mode,
                'status' => 'pending',
            ]);
        }

        $cmd = 'cd '.base_path().' && php artisan che168:parse-run '.$run->id.' > /dev/null 2>&1 &';

        if (PHP_OS_FAMILY === 'Windows') {
            Process::path(base_path())->start(['php', 'artisan', 'che168:parse-run', (string) $run->id]);
        } else {
            exec($cmd);
        }

        if ($request->expectsJson()) {
            return response()->json(['id' => $run->id]);
        }

        return redirect()->route('che168.dashboard')->with('run_id', $run->id);
    }

    public function show(ScrapeRun $run): JsonResponse
    {
        return response()->json($run->fresh());
    }

    public function active(): JsonResponse
    {
        $run = ScrapeRun::query()
            ->whereIn('status', ['pending', 'running', 'needs_captcha'])
            ->latest('id')
            ->first();

        return response()->json(['run' => $run]);
    }
}