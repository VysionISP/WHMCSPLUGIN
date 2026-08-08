<?php

namespace WHMCS\Module\Server\VirtutelNbn\Service;

use WHMCS\Database\Capsule;

/**
 * SMS sender for signup mobile verification. ClickSend REST v3 (basic
 * auth). No credentials configured => verification is skipped entirely,
 * so the onboarding wizard degrades gracefully.
 */
class Sms
{
    /** @return array{user: string, key: string, from: string} */
    public static function config(): array
    {
        $rows = Capsule::table('tbladdonmodules')
            ->where('module', 'virtutel_nbn_admin')
            ->whereIn('setting', ['clicksend_username', 'clicksend_api_key', 'sms_from'])
            ->pluck('value', 'setting');

        return [
            'user' => trim((string) ($rows['clicksend_username'] ?? '')),
            'key' => trim((string) ($rows['clicksend_api_key'] ?? '')),
            'from' => trim((string) ($rows['sms_from'] ?? '')) ?: 'Korvix',
        ];
    }

    public static function enabled(): bool
    {
        $config = self::config();

        return $config['user'] !== '' && $config['key'] !== '';
    }

    public static function send(string $to, string $message): void
    {
        $config = self::config();
        if ($config['user'] === '' || $config['key'] === '') {
            throw new \RuntimeException('SMS is not configured');
        }

        $ch = curl_init('https://rest.clicksend.com/v3/sms/send');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERPWD => $config['user'] . ':' . $config['key'],
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode(['messages' => [[
                'to' => $to,
                'body' => $message,
                'from' => substr($config['from'], 0, 11),
                'source' => 'korvix-signup',
            ]]]),
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($body === false || $status >= 400) {
            throw new \RuntimeException('SMS send failed: ' . ($err !== '' ? $err : 'HTTP ' . $status));
        }
        if (function_exists('logActivity')) {
            logActivity('Virtutel NBN: verification SMS sent to ' . substr($to, 0, 6) . '****');
        }
    }
}
