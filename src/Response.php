<?php declare(strict_types=1);
/*
 * This file is part of Aplus Framework HTTP Client Library.
 *
 * (c) Natan Felles <natanfelles@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\HTTP\Client;

use Exception;
use Framework\HTTP\Cookie;
use Framework\HTTP\Message;
use Framework\HTTP\ResponseInterface;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

/**
 * Class Response.
 *
 * @package http-client
 */
class Response extends Message implements ResponseInterface
{
    protected string $protocol;
    protected int $statusCode;
    protected string $statusReason;

    /**
     * Response constructor.
     *
     * @param string $protocol
     * @param int $status
     * @param string $reason
     * @param array<string,array<int,string>> $headers
     * @param string $body
     */
    public function __construct(
        string $protocol,
        int $status,
        string $reason,
        array $headers,
        string $body
    ) {
        $this->setProtocol($protocol);
        $this->setStatusCode($status);
        $this->setStatusReason($reason);
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $this->appendHeader($name, $value);
            }
        }
        $this->setBody($body);
    }

    #[Pure]
    public function getStatusCode() : int
    {
        return parent::getStatusCode();
    }

    /**
     * @param int $code
     *
     * @throws InvalidArgumentException if status code is invalid
     *
     * @return bool
     */
    public function hasStatusCode(int $code) : bool
    {
        return parent::hasStatusCode($code);
    }

    #[Pure]
    public function getStatusReason() : string
    {
        return $this->statusReason;
    }

    /**
     * @param string $statusReason
     *
     * @return static
     */
    protected function setStatusReason(string $statusReason) : static
    {
        $this->statusReason = $statusReason;
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @throws Exception if Cookie::setExpires fail
     *
     * @return static
     */
    protected function setHeader(string $name, string $value) : static
    {
        if (\strtolower($name) === 'set-cookie') {
            $values = \str_contains($value, "\n")
                ? \explode("\n", $value)
                : [$value];
            foreach ($values as $val) {
                $cookie = Cookie::parse($val);
                if ($cookie) {
                    $this->setCookie($cookie);
                }
            }
        }
        return parent::setHeader($name, $value);
    }

    /**
     * Get body as decoded JSON.
     *
     * @param bool $assoc
     * @param int|null $options
     * @param int $depth
     *
     * @return array<string,mixed>|false|object
     */
    public function getJson(bool $assoc = false, int $options = null, int $depth = 512) : array | object | false
    {
        if ($options === null) {
            $options = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES;
        }
        $body = \json_decode($this->getBody(), $assoc, $depth, $options);
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            return false;
        }
        return $body;
    }

    #[Pure]
    public function isJson() : bool
    {
        return $this->parseContentType() === 'application/json';
    }

    #[Pure]
    public function getStatus() : string
    {
        return $this->getStatusCode() . ' ' . $this->getStatusReason();
    }
}
