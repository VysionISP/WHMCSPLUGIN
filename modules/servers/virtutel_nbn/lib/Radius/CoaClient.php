<?php

namespace WHMCS\Module\Server\VirtutelNbn\Radius;

/**
 * Minimal RFC 5176 Dynamic Authorization client: sends a
 * Disconnect-Request (packet code 40) to the BNG so a subscriber's live
 * session drops and re-authorises against current RADIUS state.
 */
class CoaClient
{
    private const CODE_DISCONNECT_REQUEST = 40;
    private const CODE_DISCONNECT_ACK = 41;
    private const CODE_DISCONNECT_NAK = 42;

    private const ATTR_USER_NAME = 1;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $secret,
    ) {
    }

    /** @return bool true on Disconnect-ACK (or NAK — the BNG responded) */
    public function disconnect(string $username, ?bool &$acked = null): bool
    {
        if ($this->host === '' || $this->secret === '') {
            return false;
        }

        $id = random_int(0, 255);
        $packet = self::buildDisconnectPacket($id, $username, $this->secret);

        $socket = @fsockopen('udp://' . $this->host, $this->port, $errno, $errstr, 3);
        if ($socket === false) {
            return false;
        }

        try {
            stream_set_timeout($socket, 3);
            fwrite($socket, $packet);
            $response = fread($socket, 4096);
        } finally {
            fclose($socket);
        }

        if (!is_string($response) || strlen($response) < 20) {
            return false;
        }

        $code = ord($response[0]);
        $acked = $code === self::CODE_DISCONNECT_ACK;

        // A NAK still proves reachability and a valid shared secret.
        return in_array($code, [self::CODE_DISCONNECT_ACK, self::CODE_DISCONNECT_NAK], true);
    }

    /**
     * RFC 2865/5176 packet: Code, Identifier, Length, Request Authenticator
     * = MD5(Code+ID+Length+16 zero octets+Attributes+Secret), Attributes.
     */
    public static function buildDisconnectPacket(int $id, string $username, string $secret): string
    {
        $attributes = chr(self::ATTR_USER_NAME) . chr(2 + strlen($username)) . $username;
        $length = 20 + strlen($attributes);

        $header = chr(self::CODE_DISCONNECT_REQUEST) . chr($id) . pack('n', $length);
        $authenticator = md5($header . str_repeat("\0", 16) . $attributes . $secret, true);

        return $header . $authenticator . $attributes;
    }
}
