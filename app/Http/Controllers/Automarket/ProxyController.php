<?php

namespace App\Http\Controllers\Automarket;

use App\Http\Controllers\Controller;
use App\Models\AuctionProxy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProxyController extends Controller
{
    public function index(): View
    {
        return view('automarket.proxies.index', [
            'proxies' => AuctionProxy::query()->orderByDesc('enabled')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'scheme' => ['required', 'in:http,https,socks5'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
        ]);
        $data['username'] = $data['username'] !== '' ? $data['username'] : null;
        $data['password'] = $request->filled('password') ? $data['password'] : null;
        $data['enabled'] = true;

        AuctionProxy::query()->create($data);

        return redirect()->route('automarket.proxies.index')->with('ok', 'Прокси добавлен');
    }

    public function toggle(AuctionProxy $proxy): RedirectResponse
    {
        $proxy->update(['enabled' => ! $proxy->enabled]);

        return back();
    }

    public function destroy(AuctionProxy $proxy): RedirectResponse
    {
        $proxy->delete();

        return redirect()->route('automarket.proxies.index')->with('ok', 'Прокси удалён');
    }
}
