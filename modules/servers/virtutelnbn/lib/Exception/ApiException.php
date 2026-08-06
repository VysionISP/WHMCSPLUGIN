<?php

namespace Vysion\VirtutelNbn\Exception;

class ApiException extends ModuleException
{
    public function __construct(
        string $message,
        public readonly ?int $httpStatus = null,
        public readonly ?array $responseBody = null,
    ) {
        parent::__construct($message);
    }
}
