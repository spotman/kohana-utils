<?php
namespace BetaKiller\Utils;

use Throwable;

class UtilsException extends \Exception
{
    /**
     * Construct the exception. Note: The message is NOT binary safe.
     *
     * @link  http://php.net/manual/en/exception.construct.php
     *
     * @param string     $message  [optional] The Exception message to throw.
     * @param array|null $vars
     * @param int        $code     [optional] The Exception code.
     * @param Throwable  $previous [optional] The previous throwable used for the exception chaining.
     *
     * @since 5.1.0
     */
    public function __construct(string $message = '', array $vars = null, int $code = 0, \Throwable $previous = null)
    {
        if ($vars) {
            $message = strtr($message, $vars);
        }

        parent::__construct($message, $code, $previous);
    }
}
