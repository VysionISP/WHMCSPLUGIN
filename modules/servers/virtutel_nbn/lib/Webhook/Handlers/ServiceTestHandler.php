<?php

namespace WHMCS\Module\Server\VirtutelNbn\Webhook\Handlers;

use WHMCS\Module\Server\VirtutelNbn\Repository\Settings;
use WHMCS\Module\Server\VirtutelNbn\Webhook\CallbackEnvelope;

/**
 * ServiceTestStateChangeNotification: tracks diagnostic tests (NTD reset,
 * port status, line diagnostics, ...) requested from the admin service tab.
 * TestCompleted callbacks carry the results inline in
 * event.serviceTest.serviceTestResults — no follow-up GET needed.
 */
class ServiceTestHandler
{
    public function handle(CallbackEnvelope $envelope): bool
    {
        $testId = strtoupper(trim($envelope->objectId));
        if ($testId === '') {
            return false;
        }

        $serviceId = (int) (Settings::get('svctest_' . $testId, '0') ?? '0');
        if ($serviceId === 0) {
            return false; // not one of ours — raw event is stored regardless
        }

        $tests = json_decode((string) (Settings::get('tests_' . $serviceId, '') ?? ''), true);
        $tests = is_array($tests) ? $tests : [];

        $found = false;
        foreach ($tests as &$test) {
            if (($test['id'] ?? '') === $testId) {
                $test['status'] = $envelope->notificationType;
                $test['at'] = time();
                if ($envelope->notificationType === 'TestCompleted') {
                    $results = $envelope->event['serviceTest']['serviceTestResults'] ?? null;
                    if ($results !== null) {
                        $test['results'] = $results;
                    }
                }
                $found = true;
                break;
            }
        }
        unset($test);

        if (!$found) {
            $tests[] = ['id' => $testId, 'type' => '?', 'status' => $envelope->notificationType, 'at' => time()];
        }

        // Keep the last ten, newest first.
        usort($tests, fn ($a, $b) => ($b['at'] ?? 0) <=> ($a['at'] ?? 0));
        $tests = array_slice($tests, 0, 10);

        Settings::set('tests_' . $serviceId, (string) json_encode($tests));

        if (function_exists('logActivity')) {
            logActivity(sprintf(
                'Virtutel NBN: service test %s -> %s (service #%d)',
                $testId,
                $envelope->notificationType,
                $serviceId
            ));
        }

        return true;
    }
}
