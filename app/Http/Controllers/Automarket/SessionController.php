<?php

namespace App\Http\Controllers\Automarket;

use App\Exceptions\AutomarketAuthException;
use App\Exceptions\AutomarketCaptchaException;
use App\Http\Controllers\Controller;
use App\Models\AuctionProxy;
use App\Services\Automarket\SessionClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function show(SessionClient $client): View
    {
        return view('automarket.session', [
            'state' => $client->sessionState(),
            'username' => $client->username(),
            'proxy' => $client->chooseProxy(),
        ]);
    }

    public function store(Request $request, SessionClient $client): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        if ($request->filled('proxy_id')) {
            $proxy = AuctionProxy::query()->where('enabled', true)->find((int) $request->input('proxy_id'));
            if ($proxy) {
                $client->chooseProxy($proxy);
            }
        }
        $client->saveCredentials($data['username'], $data['password']);

        try {
            $client->login($data['username'], $data['password']);
        } catch (AutomarketAuthException|AutomarketCaptchaException $e) {
            return back()->withInput($request->only('username'))->withErrors(['login' => $e->getMessage()]);
        }

        return redirect()->route('automarket.session')->with('ok', 'Вход выполнен. Можно продолжить парсер.');
    }
}
