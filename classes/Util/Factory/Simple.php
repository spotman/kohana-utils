<?php defined('SYSPATH') or die('No direct access allowed.');

trait Util_Factory_Simple {

    public static function factory()
    {
        return new static;
    }

}