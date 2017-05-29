<?php
namespace BetaKiller;

class Exception extends \Kohana_Exception
{
    public function oneLiner()
    {
        return sprintf(
            '%s [ %s ]: %s ~ %s [ %d ]',
            get_class($this), $this->getCode(), strip_tags($this->getMessage()), $this->getFile(), $this->getLine()
        );
    }
}
