<?php

/**
 * Guaranteed loader for the Virtutel NBN module hooks.
 *
 * WHMCS auto-loads provisioning-module hooks.php files, but only under the
 * right activation conditions — this file (always loaded from
 * includes/hooks/) makes hook registration unconditional. The module file
 * guards against double registration.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

$virtutelNbnHooks = ROOTDIR . '/modules/servers/virtutel_nbn/hooks.php';
if (is_file($virtutelNbnHooks)) {
    require_once $virtutelNbnHooks;
}
