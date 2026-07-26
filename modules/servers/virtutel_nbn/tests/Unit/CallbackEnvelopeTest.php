<?php

namespace WHMCS\Module\Server\VirtutelNbn\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WHMCS\Module\Server\VirtutelNbn\Webhook\CallbackEnvelope;

class CallbackEnvelopeTest extends TestCase
{
    public function testParsesDocumentedEnvelope(): void
    {
        $envelope = CallbackEnvelope::fromJson(json_encode([
            'eventUuid' => 'c1043a09-44db-4a66-b00d-5caad94d657e',
            'eventTime' => '2019-11-07T14:01:07+11:00',
            'eventType' => 'ProductOrderStateChangeNotification',
            'event' => [
                'id' => 'VTORD00000000001',
                'notificationType' => 'OrderCompleted',
                'reason' => '',
            ],
        ]));

        $this->assertSame('c1043a09-44db-4a66-b00d-5caad94d657e', $envelope->eventUuid);
        $this->assertSame('VTORD00000000001', $envelope->objectId);
        $this->assertSame('OrderCompleted', $envelope->notificationType);
        $this->assertSame('order', $envelope->family());
        $this->assertSame('2019-11-07 03:01:07', $envelope->eventTimeSql());
    }

    public function testFamilyRouting(): void
    {
        $make = fn (string $type) => CallbackEnvelope::fromJson(json_encode([
            'eventUuid' => 'u-' . $type,
            'eventType' => $type,
            'event' => ['id' => 'X'],
        ]));

        $this->assertSame('order', $make('ProductOrderCreationNotification')->family());
        $this->assertSame('appointment', $make('AppointmentStateChangeNotification')->family());
        $this->assertSame('service', $make('ProductRemoveNotification')->family());
        $this->assertSame('service', $make('ProductAttributeValueChangeNotification')->family());
        $this->assertSame('health', $make('ServiceHealthStateChangeNotification')->family());
        $this->assertSame('other', $make('OutageNotification')->family());
    }

    public function testRejectsMissingUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CallbackEnvelope::fromJson('{"eventType":"x","event":{"id":"y"}}');
    }

    public function testRejectsNonJson(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CallbackEnvelope::fromJson('not json');
    }
}
