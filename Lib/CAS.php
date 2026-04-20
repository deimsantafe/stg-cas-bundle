<?php

namespace STG\DEIM\Security\Bundle\CasBundle\Lib;

class CAS
{
    protected string $server;
    protected string $url;
    protected mixed $cert;
    protected string $usernameAttribute;
    protected bool $proxy;

    public function __construct(
        string $url,
        mixed $server,
        mixed $cert,
        string $usernameAttribute,
        bool $proxy
    ) {
        $this->server            = ($server !== false && $server !== null) ? (string) $server : $url;
        $this->url               = $url;
        $this->cert              = $cert;
        $this->usernameAttribute = $usernameAttribute;
        $this->proxy             = $proxy;
    }

    public function isProxy(): bool
    {
        return $this->proxy;
    }

    public function getCert(): mixed
    {
        return $this->cert;
    }

    public function getLoginUrl(string $serviceUrl): string
    {
        return sprintf('%s/login?service=%s', $this->url, urlencode($serviceUrl));
    }

    public function getLogoutUrl(string $serviceUrl): string
    {
        return sprintf('%s/logout?service=%s', $this->url, urlencode($serviceUrl));
    }

    /**
     * @throws \Exception si se llama en modo proxy
     */
    public function getValidationUrl(string $serviceUrl, string $serviceTicket): string
    {
        if ($this->isProxy()) {
            throw new \Exception('Use getProxyValidationUrl() en modo proxy');
        }

        return sprintf(
            '%s/p3/serviceValidate?service=%s&ticket=%s',
            $this->server,
            urlencode($serviceUrl),
            urlencode($serviceTicket)
        );
    }

    /**
     * @throws \Exception si no se está en modo proxy
     */
    public function getProxyValidationUrl(string $serviceUrl, string $serviceTicket, string $proxyCallback): string
    {
        if (!$this->isProxy()) {
            throw new \Exception('Use getValidationUrl() fuera de modo proxy');
        }

        return sprintf(
            '%s/p3/proxyValidate?service=%s&ticket=%s&pgtUrl=%s',
            $this->server,
            urlencode($serviceUrl),
            urlencode($serviceTicket),
            urlencode($proxyCallback)
        );
    }

    /**
     * @throws \Exception si no se está en modo proxy
     */
    public function getProxyServiceUrl(string $serviceUrl, string $pgt): string
    {
        if (!$this->isProxy()) {
            throw new \Exception('Use este método sólo en modo proxy');
        }

        return sprintf(
            '%s/proxy?targetService=%s&pgt=%s',
            $this->server,
            urlencode($serviceUrl),
            urlencode($pgt)
        );
    }

    public function getUsernameAttribute(): string
    {
        return $this->usernameAttribute;
    }

    public function getServerUrl(): string
    {
        return $this->server;
    }
}
