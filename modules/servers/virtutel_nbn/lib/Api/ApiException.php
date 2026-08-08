<?php

namespace WHMCS\Module\Server\VirtutelNbn\Api;

/**
 * Thrown for any Virtutel API failure: transport errors, non-2xx responses,
 * or responses where vt_success is false.
 */
class ApiException extends \RuntimeException
{
    /** Virtutel's short error code (vt_short_error), if the response carried one. */
    private string $shortError;

    private int $httpStatus;

    public function __construct(string $message, string $shortError = '', int $httpStatus = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $httpStatus, $previous);
        $this->shortError = $shortError;
        $this->httpStatus = $httpStatus;
    }

    public function getShortError(): string
    {
        return $this->shortError;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function isAuthError(): bool
    {
        return $this->httpStatus === 401 || $this->shortError === 'auth_error';
    }
}
