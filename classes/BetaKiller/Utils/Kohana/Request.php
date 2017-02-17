<?php
namespace BetaKiller\Utils\Kohana;

class Request extends \Kohana_Request
{
    public function module()
    {
        return $this->param('module');
    }

    public function client_ip()
    {
        return static::$client_ip;
    }

    public function get_user_agent()
    {
        return static::$user_agent;
    }
}
