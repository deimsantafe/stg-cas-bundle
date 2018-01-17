<?php

namespace STG\DEIM\Security\Bundle\CasBundle\Lib;

class CAS
{
    /**
     * @var string
     */
    protected $server;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $cert;

    /**
     * @var string
     */
    protected $usernameAttribute;

    /**
     * @var bool
     */
    protected $proxy;

    /**
     * @param string $url
     * @param string $server
     * @param string $cert
     * @param string $usernameAttribute
     * @param boolean $proxy
     */
    public function __construct($url, $server, $cert, $usernameAttribute, $proxy)
    {
        $this->server = $server ? $server : $url;
        $this->url = $url;
        $this->cert = $cert;
        $this->usernameAttribute = $usernameAttribute;
        $this->proxy = $proxy;
    }

    /**
     * @return bool
     */
    public function isProxy()
    {
        return $this->proxy;
    }

    /**
     * @return string
     */
    public function getCert()
    {
        return $this->cert;
    }

    /**
     * @param string $serviceUrl
     * @return string
     */
    public function getLoginUrl($serviceUrl)
    {
        return sprintf('%s/login?service=%s', $this->url, $serviceUrl);
    }

    public function getLogoutUrl($serviceUrl)
    {
        return sprintf('%s/logout?service=%s', $this->url, $serviceUrl);
    }

    /**
     * @param $serviceUrl
     * @param $serviceTicket
     * @return string
     * @throws \Exception
     */
    public function getValidationUrl($serviceUrl, $serviceTicket)
    {
        if ($this->isProxy()) {
            throw new \Exception('You should not call this method in proxy mode');
        }

        return sprintf('%s/p3/serviceValidate?service=%s&ticket=%s',
            $this->server, $serviceUrl, $serviceTicket);
    }

    /**
     * @param $serviceUrl
     * @param $serviceTicket
     * @param $proxyCallback
     * @return string
     * @throws \Exception
     */
    public function getProxyValidationUrl($serviceUrl, $serviceTicket, $proxyCallback)
    {
        if (!$this->isProxy()) {
            throw new \Exception('You should call this method only in proxy mode');
        }

        return sprintf('%s/p3/proxyValidate?service=%s&ticket=%s&pgtUrl=%s',
            $this->server, $serviceUrl, $serviceTicket, $proxyCallback);
    }

    /**
     * @param $serviceUrl
     * @param $pgt
     * @return string
     * @throws \Exception
     */
    public function getProxyServiceUrl($serviceUrl, $pgt)
    {
        if (!$this->isProxy()) {
            throw new \Exception('You should call this method only in proxy mode');
        }

        return sprintf('%s/proxy?targetService=%s&pgt=%s',
            $this->server, $serviceUrl, $pgt);
    }

    /**
     * @return string
     */
    public function getUsernameAttribute()
    {
        return $this->usernameAttribute;
    }

    /**
     * @return string
     */
    public function getServerUrl()
    {
        return $this->server;
    }
}
