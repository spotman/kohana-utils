<?php
namespace BetaKiller\Utils\Kohana;

/**
 * Class Request
 *
 * @deprecated
 * @package BetaKiller\Utils\Kohana
 */
class Request extends \Kohana_Request
{
    public function module()
    {
        return $this->param('module');
    }

    public function getClientIp(): string
    {
        return static::$client_ip;
    }

    public function getUserAgent(): string
    {
        return static::$user_agent;
    }
}
