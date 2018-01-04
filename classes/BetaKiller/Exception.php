<?php
namespace BetaKiller;

class Exception extends \Kohana_Exception
{
    /**
     * @param \Throwable $e
     *
     * @return static
     */
    public static function wrap(\Throwable $e)
    {
        return new static(':error', [':error' => $e->getMessage()], $e->getCode(), $e);
    }

    public function oneLiner(): string
    {
        return sprintf(
            '%s [ %s ]: %s ~ %s [ %d ]',
            \get_class($this), $this->getCode(), \strip_tags($this->getMessage()), $this->getFile(), $this->getLine()
        );
    }
}
