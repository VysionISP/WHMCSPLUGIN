<?php

namespace WHMCS\Module\Server\VirtutelNbn\Radius;

/**
 * AAA provisioning contract. Subscribers are keyed on AVC ID (the IPoE
 * circuit ID presented via DHCP Option 82) — never PPPoE credentials.
 */
interface RadiusProvisioner
{
    /** Create/refresh the subscriber entry with speed attributes. */
    public function provision(string $avcId, string $speedTier): void;

    /** Update the speed attributes (after a Modify Speed order completes). */
    public function applySpeed(string $avcId, string $speedTier): void;

    /** Move the subscriber to the suspended profile. */
    public function suspend(string $avcId): void;

    /** Restore the subscriber to the active profile. */
    public function unsuspend(string $avcId): void;

    /** Remove the subscriber entirely. */
    public function terminate(string $avcId): void;

    /** Drop any live session so the BNG re-authorises with current state. */
    public function disconnectSession(string $avcId): bool;
}
