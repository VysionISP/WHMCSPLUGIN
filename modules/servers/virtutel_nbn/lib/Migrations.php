<?php

namespace WHMCS\Module\Server\VirtutelNbn;

use WHMCS\Database\Capsule;

/**
 * Idempotent schema management for the module's custom tables.
 *
 * WHMCS server modules have no activation hook, so ensure() is called from
 * every entrypoint. The applied schema version is cached in
 * mod_virtutel_settings (and per-request in a static) to keep it cheap.
 */
class Migrations
{
    public const SCHEMA_VERSION = 4;

    private static bool $checkedThisRequest = false;

    public static function ensure(): void
    {
        if (self::$checkedThisRequest) {
            return;
        }
        self::$checkedThisRequest = true;

        $schema = Capsule::schema();

        if (!$schema->hasTable('mod_virtutel_settings')) {
            $schema->create('mod_virtutel_settings', function ($table) {
                $table->string('name', 64)->primary();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        $current = (int) (Capsule::table('mod_virtutel_settings')
            ->where('name', 'schema_version')->value('value') ?? 0);

        if ($current >= self::SCHEMA_VERSION) {
            return;
        }

        if ($current < 1) {
            self::migrateToV1($schema);
        }
        if ($current < 2) {
            self::migrateToV2($schema);
        }
        if ($current < 3) {
            self::migrateToV3();
        }
        if ($current < 4) {
            self::migrateToV4($schema);
        }

        Capsule::table('mod_virtutel_settings')->updateOrInsert(
            ['name' => 'schema_version'],
            ['value' => (string) self::SCHEMA_VERSION, 'updated_at' => date('Y-m-d H:i:s')]
        );
    }

    /**
     * AVC into the service Domain field so WHMCS prints it on invoice
     * lines and service lists natively (backfill for already-linked
     * services; empty domains only, nothing is clobbered).
     */
    private static function migrateToV4($schema): void
    {
        if (!$schema->hasColumn('mod_virtutel_services', 'service_address')) {
            $schema->table('mod_virtutel_services', function ($table) {
                $table->string('service_address', 160)->nullable();
            });
        }

        $linked = Capsule::table('mod_virtutel_services')
            ->whereNotNull('avc_id')->where('avc_id', '!=', '')
            ->limit(100)
            ->get();
        foreach ($linked as $row) {
            Capsule::table('tblhosting')
                ->where('id', (int) $row->whmcs_service_id)
                ->where(function ($q) {
                    $q->whereNull('domain')->orWhere('domain', '');
                })
                ->update(['domain' => (string) $row->avc_id]);

            // Best-effort address backfill from the LOC ID (must never
            // block migration).
            if ((string) ($row->service_address ?? '') === ''
                && (string) ($row->nbn_location_id ?? '') !== '') {
                try {
                    $client = \WHMCS\Module\Server\VirtutelNbn\Api\ClientFactory::forWhmcsService(
                        (int) $row->whmcs_service_id
                    );
                    $address = \WHMCS\Module\Server\VirtutelNbn\Service\ServiceLinker::resolveAddress(
                        $client,
                        (string) $row->nbn_location_id
                    );
                    if ($address !== '') {
                        Capsule::table('mod_virtutel_services')
                            ->where('id', $row->id)
                            ->update(['service_address' => $address]);
                    }
                } catch (\Throwable $e) {
                    // filled lazily on next link/completion instead
                }
            }
        }
    }

    /** Appointment-required customer email template (admin-editable). */
    private static function migrateToV3(): void
    {
        $name = 'Virtutel NBN Appointment Required';
        $exists = Capsule::table('tblemailtemplates')
            ->where('type', 'product')->where('name', $name)->exists();
        if ($exists) {
            return;
        }

        Capsule::table('tblemailtemplates')->insert([
            'type' => 'product',
            'name' => $name,
            'subject' => 'Action needed: book your NBN installation appointment',
            'message' => '<p>Hi {$client_first_name},</p>'
                . '<p>{$appointment_reason}</p>'
                . '<p>Booking only takes a minute — pick a time that suits you here:</p>'
                . '<p><a href="{$appointment_link}">Choose your appointment time</a></p>'
                . '<p>Someone over 18 will need to be at the property during the appointment window. '
                . 'If none of the available times work, just reply to this email and we\'ll help.</p>'
                . '<p>{$signature}</p>',
            'custom' => 1,
            'disabled' => 0,
            'language' => '',
            'plaintext' => 0,
        ]);
    }

    /** Per-IP rate limiting for the public qualification endpoint. */
    private static function migrateToV2($schema): void
    {
        if (!$schema->hasTable('mod_virtutel_ratelimit')) {
            $schema->create('mod_virtutel_ratelimit', function ($table) {
                $table->string('ip', 45);
                $table->unsignedInteger('bucket'); // unix time / window
                $table->unsignedInteger('hits')->default(0);
                $table->primary(['ip', 'bucket']);
            });
        }
    }

    private static function migrateToV1($schema): void
    {
        if (!$schema->hasTable('mod_virtutel_tokens')) {
            $schema->create('mod_virtutel_tokens', function ($table) {
                $table->increments('id');
                $table->string('environment', 16);
                $table->string('api_identity', 64); // sha256 of base URL + client_id
                $table->text('access_token');       // encrypted at rest
                $table->dateTime('expires_at');
                $table->timestamps();
                $table->unique(['environment', 'api_identity']);
            });
        }

        if (!$schema->hasTable('mod_virtutel_services')) {
            $schema->create('mod_virtutel_services', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('whmcs_service_id')->unique();
                $table->string('vt_service_id', 32)->nullable()->index();
                $table->string('avc_id', 32)->nullable()->index();
                $table->string('nbn_location_id', 32)->nullable()->index();
                $table->string('technology_type', 16)->nullable();
                $table->unsignedTinyInteger('service_class')->nullable();
                $table->string('network_layer', 8)->default('layer2');
                $table->string('speed_tier', 32)->nullable();
                $table->string('carrier_status', 64)->nullable();
                $table->string('external_ref', 64)->nullable();
                $table->timestamps();
            });
        }

        if (!$schema->hasTable('mod_virtutel_orders')) {
            $schema->create('mod_virtutel_orders', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('service_id')->index();
                $table->string('order_type', 24); // connect / churn / modify_speed / disconnect
                $table->string('vt_order_id', 32)->nullable()->unique();
                $table->string('status', 64)->nullable();
                $table->string('whmcs_status', 32)->default('pending');
                $table->string('action_required', 48)->nullable();
                $table->mediumText('request_payload')->nullable();
                $table->mediumText('response_payload')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!$schema->hasTable('mod_virtutel_appointments')) {
            $schema->create('mod_virtutel_appointments', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('order_id')->index();
                $table->string('appointment_id', 64)->nullable()->index();
                $table->string('status', 32)->default('created');
                $table->dateTime('slot_start')->nullable();
                $table->dateTime('slot_end')->nullable();
                $table->string('demand_type', 32)->nullable();
                $table->timestamps();
            });
        }

        if (!$schema->hasTable('mod_virtutel_callback_events')) {
            $schema->create('mod_virtutel_callback_events', function ($table) {
                $table->increments('id');
                $table->string('event_uuid', 64)->unique();
                $table->dateTime('event_time')->nullable();
                $table->string('event_type', 96)->nullable();
                $table->string('notification_type', 96)->nullable();
                $table->string('vt_object_id', 64)->nullable()->index();
                $table->unsignedInteger('order_id')->nullable()->index();
                $table->unsignedInteger('service_id')->nullable()->index();
                $table->mediumText('payload')->nullable();
                $table->boolean('auth_ok')->default(false);
                $table->string('status', 16)->default('received');
                $table->dateTime('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }
}
