<?php

namespace App\Services\Che168;

/**
 * Tencent EdgeOne first-stage bot check (inline JS on global.che168.com).
 * Sets __tst_status and EO_Bot_Ssid before the captcha interstitial.
 */
final class EdgeOneChallenge
{
    /** @return array{__tst_status: string, EO_Bot_Ssid: string} */
    public static function botCookies(): array
    {
        return [
            '__tst_status' => self::tstStatus().'#',
            'EO_Bot_Ssid' => '1291124736',
        ];
    }

    public static function cookieHeader(?string $captchaToken = null): string
    {
        $parts = [];
        foreach (self::botCookies() as $name => $value) {
            $parts[] = $name.'='.$value;
        }
        if (filled($captchaToken)) {
            $parts[] = 'EO-Bot-Captcha-Token='.$captchaToken;
        }

        return implode('; ', $parts);
    }

    private static function tstStatus(): string
    {
        // Port of obfuscated EO challenge (constant for current script build).
        return (string) (355370132 + 19683478 + 4522257);
    }
}