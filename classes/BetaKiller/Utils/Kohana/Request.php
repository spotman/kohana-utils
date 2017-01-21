<?php
namespace BetaKiller\Utils\Kohana;

class Request extends \Kohana_Request
{
    public function module()
    {
        return $this->param('module');
    }

    public static function client_ip()
    {
        return static::$client_ip;
    }
}
