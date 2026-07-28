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
    public const SCHEMA_VERSION = 9;

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
        if ($current < 5) {
            self::migrateToV5();
        }
        if ($current < 6) {
            self::migrateToV6();
        }
        if ($current < 7) {
            self::migrateToV7($schema);
        }
        if ($current < 8) {
            self::migrateToV8();
        }
        if ($current < 9) {
            self::migrateToV9();
        }

        Capsule::table('mod_virtutel_settings')->updateOrInsert(
            ['name' => 'schema_version'],
            ['value' => (string) self::SCHEMA_VERSION, 'updated_at' => date('Y-m-d H:i:s')]
        );
    }

    /**
     * Branded global email header/footer (dark Korvix wrapper around every
     * HTML mail WHMCS sends). Only installed into EMPTY settings — an
     * admin-customised header/footer is never overwritten — and only for
     * setting names that exist on this install (WHMCS naming varies by
     * version; when absent, paste modules/servers/virtutel_nbn/data/email/*.html manually).
     */
    private static function migrateToV5(): void
    {
        $dir = __DIR__ . '/../data/email';
        $pairs = [
            'EmailGlobalHeader' => $dir . '/global-header.html',
            'EmailGlobalFooter' => $dir . '/global-footer.html',
        ];

        foreach ($pairs as $setting => $file) {
            try {
                if (!is_file($file)) {
                    continue;
                }
                $row = Capsule::table('tblconfiguration')->where('setting', $setting)->first();
                if (!$row || trim((string) ($row->value ?? '')) !== '') {
                    continue; // setting unknown on this install, or already customised
                }
                $html = (string) file_get_contents($file);
                // strip the instructional comment block at the top
                $html = trim((string) preg_replace('/^<!--.*?-->\s*/s', '', $html));
                if ($html === '') {
                    continue;
                }
                Capsule::table('tblconfiguration')->where('setting', $setting)->update([
                    'value' => $html,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $e) {
                // cosmetic — never block migration
            }
        }
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

    /** Date of Birth client custom field, filled by the signup wizard. */
    private static function migrateToV8(): void
    {
        $exists = Capsule::table('tblcustomfields')
            ->where('type', 'client')
            ->where('fieldname', 'like', 'Date of Birth%')
            ->exists();
        if ($exists) {
            return;
        }

        Capsule::table('tblcustomfields')->insert([
            'type' => 'client',
            'relid' => 0,
            'fieldname' => 'Date of Birth',
            'fieldtype' => 'text',
            'description' => 'YYYY-MM-DD (collected at signup for identity checks)',
            'fieldoptions' => '',
            'regexpr' => '',
            'adminonly' => '',
            'required' => '',
            'showorder' => '',
            'showinvoice' => '',
            'sortorder' => 0,
        ]);
    }

    /** Launch-interest leads captured from the coming-soon pages. */
    private static function migrateToV7($schema): void
    {
        if (!$schema->hasTable('mod_virtutel_leads')) {
            $schema->create('mod_virtutel_leads', function ($table) {
                $table->increments('id');
                $table->string('product', 24);
                $table->string('email', 190);
                $table->string('ip', 45)->nullable();
                $table->timestamps();
                $table->unique(['product', 'email']);
            });
        }
    }

    /** "You're connected" activation welcome email (admin-editable). */
    private static function migrateToV6(): void
    {
        $name = 'Virtutel NBN Service Activated';
        $exists = Capsule::table('tblemailtemplates')
            ->where('type', 'product')->where('name', $name)->exists();
        if ($exists) {
            return;
        }

        Capsule::table('tblemailtemplates')->insert([
            'type' => 'product',
            'name' => $name,
            'subject' => 'You\'re connected — your Korvix NBN is now active!',
            'message' => '<p>Hi {$client_first_name},</p>'
                . '<p>Great news — your NBN service is active and ready to go. '
                . 'Getting online takes about two minutes:</p>'
                . '<ol>'
                . '<li><strong>Plug in your router.</strong> Connect its WAN/Internet port to the '
                . 'NBN connection box (use port <strong>UNI-D 1</strong> unless we\'ve told you '
                . 'otherwise). On FTTN/FTTB, plug the router\'s DSL port into the phone wall socket '
                . 'instead.</li>'
                . '<li><strong>No username or password.</strong> Set the router\'s internet/WAN mode '
                . 'to <strong>DHCP / Automatic IP</strong> — Korvix connections authenticate '
                . 'automatically.</li>'
                . '<li><strong>Restart the router</strong> and give it a couple of minutes.</li>'
                . '</ol>'
                . '<p><strong>Your connection details</strong><br>'
                . 'Plan speed: {$nbn_speed}<br>'
                . 'Service ID (AVC): {$nbn_avc}<br>'
                . 'Address: {$nbn_address}</p>'
                . '<p>Keep your AVC ID handy — it identifies your line if you ever contact us '
                . '(or another provider) about this service.</p>'
                . '<p>Not online after 10 minutes? Reply to this email or call us and we\'ll get '
                . 'you sorted.</p>'
                . '<p>{$signature}</p>',
            'custom' => 1,
            'disabled' => 0,
            'language' => '',
            'plaintext' => 0,
        ]);
    }

    /**
     * Signup-wizard welcome email (admin-editable, general type): the
     * wizard creates accounts with a random password the customer never
     * sees, so this points them at the password-reset page to set their
     * own, and sets expectations for what happens next.
     */
    private static function migrateToV9(): void
    {
        $name = 'Korvix Account Welcome';
        $exists = Capsule::table('tblemailtemplates')
            ->where('type', 'general')->where('name', $name)->exists();
        if ($exists) {
            return;
        }

        Capsule::table('tblemailtemplates')->insert([
            'type' => 'general',
            'name' => $name,
            'subject' => 'Welcome to Korvix — set your account password',
            'message' => '<p>Hi {$first_name},</p>'
                . '<p>Your Korvix account is ready — we created it while you were signing up '
                . 'for your NBN service.</p>'
                . '<p><strong>One thing to do:</strong> set your account password so you can '
                . 'log in to the portal any time. It takes 30 seconds — just enter your email '
                . 'address here and follow the link we send you:</p>'
                . '<p><a href="{$reset_url}">Set my password</a></p>'
                . '<p><strong>What happens next?</strong> We\'re lodging your order with the '
                . 'carrier now. We\'ll email you at every step — and if a technician visit or '
                . 'anything else is needed, we\'ll let you know straight away. You can also '
                . 'track everything in <a href="{$portal_url}">your portal</a>.</p>'
                . '<p>Questions? Reply to this email or call us on 03 4130 5013 — a human in '
                . 'Gippsland picks up.</p>'
                . '<p>{$signature}</p>',
            'custom' => 1,
            'disabled' => 0,
            'language' => '',
            'plaintext' => 0,
        ]);
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
