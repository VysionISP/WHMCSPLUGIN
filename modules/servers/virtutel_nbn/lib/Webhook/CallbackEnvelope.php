<?php

namespace WHMCS\Module\Server\VirtutelNbn\Webhook;

/**
 * Parsed Virtutel callback payload.
 *
 * Envelope contract (docs/api/02_callbacks.md): eventUuid, eventTime
 * (ISO8601), eventType, and event {id, notificationType, reason, ...}.
 */
class CallbackEnvelope
{
    public function __construct(
        public readonly string $eventUuid,
        public readonly string $eventTime,
        public readonly string $eventType,
        public readonly string $objectId,
        public readonly string $notificationType,
        public readonly string $reason,
        public readonly array $event,
        public readonly array $raw,
    ) {
    }

    /**
     * @throws \InvalidArgumentException on a payload that doesn't match the envelope
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Callback payload is not valid JSON');
        }

        $uuid = $data['eventUuid'] ?? '';
        $event = $data['event'] ?? null;
        if (!is_string($uuid) || $uuid === '' || !is_array($event)) {
            throw new \InvalidArgumentException('Callback payload missing eventUuid or event object');
        }

        return new self(
            $uuid,
            (string) ($data['eventTime'] ?? ''),
            (string) ($data['eventType'] ?? ''),
            (string) ($event['id'] ?? ''),
            (string) ($event['notificationType'] ?? ''),
            (string) ($event['reason'] ?? ''),
            $event,
            $data,
        );
    }

    /** Best-effort MySQL datetime from the ISO8601 eventTime. */
    public function eventTimeSql(): ?string
    {
        $ts = strtotime($this->eventTime);

        return $ts === false ? null : date('Y-m-d H:i:s', $ts);
    }

    /** Event family used for handler routing, derived from eventType. */
    public function family(): string
    {
        $type = $this->eventType;

        return match (true) {
            str_starts_with($type, 'ProductOrder') => 'order',
            str_starts_with($type, 'Appointment') => 'appointment',
            // ProductAttributeValueChangeNotification / ProductRemoveNotification
            str_starts_with($type, 'Product') => 'service',
            default => 'other',
        };
    }
}
