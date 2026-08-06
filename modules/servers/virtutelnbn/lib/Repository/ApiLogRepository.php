<?php

namespace Vysion\VirtutelNbn\Repository;

use WHMCS\Database\Capsule;

final class ApiLogRepository
{
    private const TABLE = 'mod_virtutel_api_log';

    /**
     * @param array|string|null $requestPayload  already-redacted payload
     * @param array|string|null $responsePayload already-redacted payload
     */
    public function record(
        string $method,
        string $endpoint,
        ?int $httpStatus,
        array|string|null $requestPayload,
        array|string|null $responsePayload,
        int $durationMs,
    ): void {
        try {
            Capsule::table(self::TABLE)->insert([
                'method' => strtoupper($method),
                'endpoint' => mb_substr($endpoint, 0, 255),
                'http_status' => $httpStatus,
                'request_payload' => is_array($requestPayload) ? json_encode($requestPayload) : $requestPayload,
                'response_payload' => is_array($responsePayload) ? json_encode($responsePayload) : $responsePayload,
                'duration_ms' => $durationMs,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Logging must never break provisioning.
        }
    }
}
