<?php

namespace WHMCS\Module\Server\VirtutelNbn\Radius;

use WHMCS\Module\Server\VirtutelNbn\Service\SpeedTier;

/**
 * FreeRADIUS SQL-backend provisioning: writes radcheck / radreply /
 * radusergroup rows keyed on the AVC ID, and drops live sessions via CoA.
 *
 * Authorisation model: the BNG presents the AVC ID (Option 82 circuit ID)
 * as User-Name; the radcheck entry accepts it, radreply carries the
 * rate-limit attribute, and radusergroup selects the active or suspended
 * profile group (walled-garden vs hard-reject is defined on the group in
 * FreeRADIUS itself).
 */
class FreeRadiusSqlProvisioner implements RadiusProvisioner
{
    private ?\PDO $pdo = null;

    public function __construct(private readonly array $config)
    {
    }

    public function provision(string $avcId, string $speedTier): void
    {
        $pdo = $this->pdo();
        $rate = self::speedValue($speedTier, $this->config['rate_limit_format']);

        $pdo->beginTransaction();
        try {
            $this->upsertPair($pdo, 'radcheck', $avcId, 'Auth-Type', ':=', 'Accept');
            $this->upsertPair($pdo, 'radreply', $avcId, $this->config['rate_limit_attr'], ':=', $rate);

            $pdo->prepare('DELETE FROM radusergroup WHERE username = ?')->execute([$avcId]);
            $pdo->prepare('INSERT INTO radusergroup (username, groupname, priority) VALUES (?, ?, 1)')
                ->execute([$avcId, $this->config['default_group']]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function applySpeed(string $avcId, string $speedTier): void
    {
        $rate = self::speedValue($speedTier, $this->config['rate_limit_format']);
        $this->upsertPair($this->pdo(), 'radreply', $avcId, $this->config['rate_limit_attr'], ':=', $rate);
    }

    public function suspend(string $avcId): void
    {
        $this->setGroup($avcId, $this->config['suspend_group']);
    }

    public function unsuspend(string $avcId): void
    {
        $this->setGroup($avcId, $this->config['default_group']);
    }

    public function terminate(string $avcId): void
    {
        $pdo = $this->pdo();
        foreach (['radcheck', 'radreply', 'radusergroup'] as $table) {
            $pdo->prepare("DELETE FROM {$table} WHERE username = ?")->execute([$avcId]);
        }
    }

    public function disconnectSession(string $avcId): bool
    {
        $coa = new CoaClient(
            $this->config['coa_host'],
            (int) $this->config['coa_port'],
            $this->config['coa_secret']
        );

        return $coa->disconnect($avcId);
    }

    /** True when the subscriber row exists. */
    public function exists(string $avcId): bool
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM radcheck WHERE username = ?');
        $stmt->execute([$avcId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Current AAA state for the admin service tab.
     *
     * @return array{group: string, rate: string}|null null when the
     *         subscriber is not provisioned at all
     */
    public function status(string $avcId): ?array
    {
        if (!$this->exists($avcId)) {
            return null;
        }

        $pdo = $this->pdo();
        $group = $pdo->prepare(
            'SELECT groupname FROM radusergroup WHERE username = ? ORDER BY priority LIMIT 1'
        );
        $group->execute([$avcId]);
        $rate = $pdo->prepare(
            'SELECT value FROM radreply WHERE username = ? AND attribute = ? LIMIT 1'
        );
        $rate->execute([$avcId, $this->config['rate_limit_attr']]);

        return [
            'group' => (string) ($group->fetchColumn() ?: ''),
            'rate' => (string) ($rate->fetchColumn() ?: ''),
        ];
    }

    /** Connectivity/schema check for the admin Test button. */
    public function healthCheck(): array
    {
        $pdo = $this->pdo();
        $missing = [];
        foreach (['radcheck', 'radreply', 'radusergroup', 'radacct'] as $table) {
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?'
            );
            $stmt->execute([$this->config['db_name'], $table]);
            if ((int) $stmt->fetchColumn() === 0) {
                $missing[] = $table;
            }
        }

        return ['connected' => true, 'missing_tables' => $missing];
    }

    /**
     * Render the rate-limit attribute value from a Virtutel speed enum.
     * Placeholders: {down} {up} (Mbps), {down_k} {up_k} (Kbps).
     * Default Mikrotik-Rate-Limit format is "{up}M/{down}M" (tx/rx from
     * the subscriber's perspective).
     */
    public static function speedValue(string $speedTier, string $format): string
    {
        $tier = SpeedTier::describe($speedTier);
        if ($tier === null) {
            throw new \InvalidArgumentException("Unknown speed tier enum '{$speedTier}'");
        }

        return strtr($format, [
            '{down}' => (string) $tier['down'],
            '{up}' => (string) $tier['up'],
            '{down_k}' => (string) ($tier['down'] * 1000),
            '{up_k}' => (string) ($tier['up'] * 1000),
        ]);
    }

    private function setGroup(string $avcId, string $group): void
    {
        $pdo = $this->pdo();
        $updated = $pdo->prepare('UPDATE radusergroup SET groupname = ? WHERE username = ?');
        $updated->execute([$group, $avcId]);
        if ($updated->rowCount() === 0) {
            $pdo->prepare('INSERT INTO radusergroup (username, groupname, priority) VALUES (?, ?, 1)')
                ->execute([$avcId, $group]);
        }
    }

    private function upsertPair(\PDO $pdo, string $table, string $username, string $attribute, string $op, string $value): void
    {
        $updated = $pdo->prepare("UPDATE {$table} SET op = ?, value = ? WHERE username = ? AND attribute = ?");
        $updated->execute([$op, $value, $username, $attribute]);
        if ($updated->rowCount() === 0) {
            $exists = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE username = ? AND attribute = ?");
            $exists->execute([$username, $attribute]);
            if ((int) $exists->fetchColumn() === 0) {
                $pdo->prepare("INSERT INTO {$table} (username, attribute, op, value) VALUES (?, ?, ?, ?)")
                    ->execute([$username, $attribute, $op, $value]);
            }
        }
    }

    private function pdo(): \PDO
    {
        if ($this->pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $this->config['db_host'],
                (int) $this->config['db_port'],
                $this->config['db_name']
            );
            $this->pdo = new \PDO($dsn, $this->config['db_user'], $this->config['db_pass'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 5,
            ]);
        }

        return $this->pdo;
    }
}
