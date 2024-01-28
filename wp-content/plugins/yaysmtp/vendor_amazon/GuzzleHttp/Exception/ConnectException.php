<?php

namespace YaySMTP\Aws3\GuzzleHttp\Exception;

use YaySMTP\Aws3\Psr\Http\Message\RequestInterface;
/**
 * Exception thrown when a connection cannot be established.
 *
 * Note that no response is present for a ConnectException
 */
class ConnectException extends \YaySMTP\Aws3\GuzzleHttp\Exception\RequestException
{
    public function __construct($message, \YaySMTP\Aws3\Psr\Http\Message\RequestInterface $request, \Exception $previous = null, array $handlerContext = [])
    {
        parent::__construct($message, $request, null, $previous, $handlerContext);
    }
    /**
     * @return null
     */
    public function getResponse()
    {
        return null;
    }
    /**
     * @return bool
     */
    public function hasResponse()
    {
        return false;
    }
}