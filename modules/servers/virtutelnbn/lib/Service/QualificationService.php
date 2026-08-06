<?php

namespace Vysion\VirtutelNbn\Service;

use Vysion\VirtutelNbn\Config;
use Vysion\VirtutelNbn\Exception\QualificationFailedException;
use Vysion\VirtutelNbn\Provider\ProviderInterface;

/**
 * NBN service qualification: resolves the order subject (LOC id preferred,
 * free-text address as fallback) and confirms the location is serviceable
 * before an order is placed.
 */
final class QualificationService
{
    public function __construct(private readonly ProviderInterface $provider)
    {
    }

    /**
     * @return array{subject: array{loc_id?: string, address?: string}, qualification: array}
     */
    public function qualify(Config $config): array
    {
        $subject = array_filter([
            'loc_id' => $config->nbnLocationId(),
            'address' => $config->serviceAddress(),
        ]);

        if ($subject === []) {
            throw new QualificationFailedException(
                'No NBN Location ID or Service Address custom field is set on this service.'
            );
        }

        $qualification = $this->provider->qualify($subject);

        // TODO(virtutel-spec): confirm the serviceability flag field name.
        $serviceable = (bool) ($qualification['serviceable'] ?? $qualification['qualified'] ?? true);
        if (!$serviceable) {
            $reason = (string) ($qualification['reason'] ?? 'location is not serviceable');
            throw new QualificationFailedException('Service qualification failed: ' . $reason);
        }

        // Prefer the canonical LOC id returned by the SQ over user input.
        if (!empty($qualification['loc_id'])) {
            $subject['loc_id'] = (string) $qualification['loc_id'];
        }

        return ['subject' => $subject, 'qualification' => $qualification];
    }
}
