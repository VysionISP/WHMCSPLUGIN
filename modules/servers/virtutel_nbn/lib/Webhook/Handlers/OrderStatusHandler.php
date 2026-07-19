<?php

namespace WHMCS\Module\Server\VirtutelNbn\Webhook\Handlers;

use WHMCS\Database\Capsule;
use WHMCS\Module\Server\VirtutelNbn\Service\OrderCompletion;
use WHMCS\Module\Server\VirtutelNbn\Service\StatusMapper;
use WHMCS\Module\Server\VirtutelNbn\Webhook\CallbackEnvelope;

/**
 * Applies ProductOrder* callbacks to mod_virtutel_orders and, on terminal
 * events, to the linked service row.
 *
 * WHMCS-side activation (flipping the service to Active, RADIUS
 * provisioning) hangs off the same terminal transition and is wired in by
 * ProvisioningService in build step 3 — this handler owns the state
 * bookkeeping that step builds on.
 */
class OrderStatusHandler
{
    public function handle(CallbackEnvelope $envelope): bool
    {
        $vtOrderId = $envelope->objectId;
        if ($vtOrderId === '') {
            return false;
        }

        $order = Capsule::table('mod_virtutel_orders')
            ->where('vt_order_id', $vtOrderId)
            ->first();

        $now = date('Y-m-d H:i:s');
        $notification = $envelope->notificationType;
        $state = StatusMapper::orderState($notification);

        if (!$order) {
            // Order we don't know (placed outside WHMCS, or import pending):
            // keep the event linked for the import/reconciliation tooling.
            if (function_exists('logActivity')) {
                logActivity(sprintf(
                    'Virtutel NBN: callback %s for unknown order %s (%s)',
                    $envelope->eventUuid,
                    $vtOrderId,
                    $notification
                ));
            }

            return false;
        }

        $update = [
            'status' => substr($notification, 0, 64),
            'whmcs_status' => $state,
            'action_required' => StatusMapper::actionRequired($notification),
            'updated_at' => $now,
        ];
        if ($state === StatusMapper::COMPLETE || $state === StatusMapper::CANCELLED) {
            $update['completed_at'] = $now;
        }

        Capsule::table('mod_virtutel_orders')->where('id', $order->id)->update($update);

        Capsule::table('mod_virtutel_callback_events')
            ->where('event_uuid', $envelope->eventUuid)
            ->update(['order_id' => $order->id, 'service_id' => $order->service_id]);

        if ($state === StatusMapper::ACTION_REQUIRED) {
            $this->raiseActionTodo($order, $notification, $envelope->reason);
        }

        $completion = new OrderCompletion();
        if (StatusMapper::isTerminalSuccess($notification)) {
            $completion->complete($order);
        } elseif (StatusMapper::isTerminalFailure($notification)) {
            $completion->cancelled($order, $envelope->reason);
        }

        return true;
    }

    /**
     * Action-required states must never stall silently: raise a WHMCS To-Do
     * so staff see it in the admin dashboard.
     */
    private function raiseActionTodo(object $order, string $notification, string $reason): void
    {
        if (!function_exists('localAPI')) {
            return;
        }

        $description = sprintf(
            'Virtutel order %s needs action: %s%s (service #%d)',
            $order->vt_order_id,
            $notification,
            $reason !== '' ? ' — ' . $reason : '',
            $order->service_id
        );

        localAPI('AddTodoItem', [
            'date' => date('Y-m-d'),
            'title' => 'Virtutel NBN: ' . $notification,
            'description' => $description,
            'status' => 'Pending',
            'duedate' => date('Y-m-d', strtotime('+2 days')),
        ]);
    }
}
