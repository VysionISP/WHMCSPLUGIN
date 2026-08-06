<?php

namespace Vysion\VirtutelNbn;

use Illuminate\Database\Schema\Blueprint;
use WHMCS\Database\Capsule;

/**
 * Creates the module's database tables. Server modules have no activation
 * hook, so ensureInstalled() is invoked lazily from every entry point and
 * is a no-op once the tables exist.
 */
final class Installer
{
    public static function ensureInstalled(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        $schema = Capsule::schema();

        if (!$schema->hasTable('mod_virtutel_service')) {
            $schema->create('mod_virtutel_service', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('whmcs_service_id')->unique();
                $table->string('provider', 32)->default('virtutel');
                $table->string('provider_service_id', 64)->nullable()->index();
                $table->string('avc_id', 64)->nullable();
                $table->string('nbn_loc_id', 32)->nullable();
                $table->string('plan_code', 64)->nullable();
                $table->string('status', 32)->default('pending');
                $table->timestamps();
            });
        }

        if (!$schema->hasTable('mod_virtutel_order')) {
            $schema->create('mod_virtutel_order', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('service_id')->index();
                $table->string('provider_order_id', 64)->nullable()->index();
                $table->string('type', 32); // connect|modify|disconnect|suspend|unsuspend
                $table->string('status', 32)->default('submitted');
                $table->text('request_payload')->nullable();
                $table->text('response_payload')->nullable();
                $table->timestamps();
            });
        }

        if (!$schema->hasTable('mod_virtutel_webhook_event')) {
            $schema->create('mod_virtutel_webhook_event', function (Blueprint $table) {
                $table->increments('id');
                $table->string('provider', 32)->default('virtutel');
                $table->string('external_event_id', 128)->unique();
                $table->string('event_type', 64)->nullable();
                $table->text('raw_payload');
                $table->boolean('signature_valid')->default(false);
                $table->string('status', 16)->default('pending'); // pending|processed|failed|skipped
                $table->unsignedInteger('order_id')->nullable()->index();
                $table->text('error')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamp('processed_at')->nullable();
            });
        }

        if (!$schema->hasTable('mod_virtutel_api_log')) {
            $schema->create('mod_virtutel_api_log', function (Blueprint $table) {
                $table->increments('id');
                $table->string('method', 8);
                $table->string('endpoint', 255);
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->text('request_payload')->nullable();
                $table->text('response_payload')->nullable();
                $table->unsignedInteger('duration_ms')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }
}
