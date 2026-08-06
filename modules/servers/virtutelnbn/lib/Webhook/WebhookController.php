<?php

namespace Vysion\VirtutelNbn\Webhook;

use Vysion\VirtutelNbn\Installer;
use Vysion\VirtutelNbn\Provider\Virtutel\VirtutelWebhookVerifier;
use Vysion\VirtutelNbn\Repository\WebhookEventRepository;
use WHMCS\Database\Capsule;

/**
 * Inbound webhook handling: verify the signature, deduplicate, persist,
 * return 200 quickly. All real processing happens asynchronously in
 * EventProcessor (WHMCS cron) so the endpoint stays fast and idempotent.
 */
final class WebhookController
{
    public function __construct(private readonly WebhookEventRepository $events)
    {
    }

    /**
     * @param array<string,string> $headers normalised lowercase header map
     * @return array{0: int, 1: array} [httpStatus, responseBody]
     */
    public function handle(string $rawBody, array $headers): array
    {
        Installer::ensureInstalled();

        $secret = self::webhookSecret();
        if ($secret === '') {
            return [503, ['error' => 'webhook secret not configured']];
        }

        $signature = $headers[strtolower(VirtutelWebhookVerifier::SIGNATURE_HEADER)] ?? '';
        $timestamp = $headers[strtolower(VirtutelWebhookVerifier::TIMESTAMP_HEADER)] ?? '';

        $verifier = new VirtutelWebhookVerifier($secret);
        if (!$verifier->verify($rawBody, $signature, $timestamp)) {
            // No detail leaked about which check failed.
            return [401, ['error' => 'invalid signature']];
        }

        $payload = json_decode($rawBody, true);
        if (!is_array($payload)) {
            return [400, ['error' => 'invalid JSON payload']];
        }

        // TODO(virtutel-spec): confirm event id / type field names.
        $eventId = (string) ($payload['event_id'] ?? $payload['id'] ?? '');
        if ($eventId === '') {
            return [400, ['error' => 'missing event id']];
        }

        $rowId = $this->events->storeIfNew([
            'provider' => 'virtutel',
            'external_event_id' => $eventId,
            'event_type' => mb_substr((string) ($payload['event_type'] ?? $payload['type'] ?? ''), 0, 64),
            'raw_payload' => $rawBody,
            'signature_valid' => 1,
            'status' => 'pending',
        ]);

        // Duplicate deliveries still get a 200 so the sender stops retrying.
        return [200, ['status' => 'accepted', 'duplicate' => $rowId === null]];
    }

    /**
     * The webhook secret lives in the Access Hash field of the module's
     * WHMCS server record (there is exactly one VirtuTel server entry in
     * a normal install; the first active one wins).
     */
    public static function webhookSecret(): string
    {
        $server = Capsule::table('tblservers')
            ->where('type', 'virtutelnbn')
            ->where('disabled', 0)
            ->orderBy('id')
            ->first();

        return trim((string) ($server->accesshash ?? ''));
    }
}
