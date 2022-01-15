<?php

declare(strict_types=1);

namespace ArieTimmerman\Laravel\SAML\SAML2;

use Exception;
use Psr\Log\LoggerInterface;
use SAML2\Compat\AbstractContainer;

class Container extends AbstractContainer
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;


    /**
     * {@inheritdoc}
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger() : LoggerInterface
    {
        return $this->logger;
    }


    /**
     * {@inheritdoc}
     * @return string
     */
    public function generateId() : string
    {
        return '_' . bin2hex(openssl_random_pseudo_bytes((43 - 1) / 2));
    }


    /**
     * {@inheritdoc}
     * @param mixed $message
     * @param string $type
     * @return void
     */
    public function debugMessage($message, string $type) : void
    {
        throw new Exception("not supported");
    }


    /**
     * {@inheritdoc}
     * @param string $url
     * @param array $data
     * @return void
     */
    public function redirect(string $url, array $data = []) : void
    {
        throw new Exception("not supported");
    }


    /**
     * {@inheritdoc}
     * @param string $url
     * @param array $data
     * @return void
     */
    public function postRedirect(string $url, array $data = []) : void
    {
        throw new Exception("not supported");
    }


    /**
     * {@inheritdoc}
     * @return string
     */
    public function getTempDir() : string
    {
        throw new Exception("not supported");
    }


    /**
     * {@inheritdoc}
     * @param string $filename
     * @param string $date
     * @param int|null $mode
     * @return void
     */
    public function writeFile(string $filename, string $data, int $mode = null) : void
    {
        throw new Exception("not supported");
    }
}
