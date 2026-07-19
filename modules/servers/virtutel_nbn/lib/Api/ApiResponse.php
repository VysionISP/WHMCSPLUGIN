<?php

namespace WHMCS\Module\Server\VirtutelNbn\Api;

/**
 * Decoded Virtutel API response. Every Virtutel response carries the
 * vt_success / vt_short_error / vt_error_desc envelope at the root.
 */
class ApiResponse
{
    public function __construct(
        public readonly int $status,
        public readonly array $data,
        public readonly array $headers = [],
    ) {
    }

    public function vtSuccess(): bool
    {
        return (bool) ($this->data['vt_success'] ?? false);
    }

    public function vtShortError(): string
    {
        return (string) ($this->data['vt_short_error'] ?? '');
    }

    public function vtErrorDesc(): string
    {
        return (string) ($this->data['vt_error_desc'] ?? '');
    }

    /**
     * Fetch a value by dot-notation path, e.g. get('responseData.0.locId').
     */
    public function get(string $path, mixed $default = null): mixed
    {
        $value = $this->data;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}
