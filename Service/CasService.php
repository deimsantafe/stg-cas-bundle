<?php

namespace Stg\Bundle\CasGuardBundle\Service;

use Stg\Bundle\CasGuardBundle\Exception\CasException;
use phpCAS;

class CasService
{
    private $configuration;
    private $logFile;

    public function __construct(
                        array $configuration,
                        string $logFile)
    {
        $this->configuration = $configuration;
        $this->logFile = $logFile;
        $this->initPhpCas();
    }

    protected function initPhpCas()
    {
        phpCAS::setDebug($this->getDebug());
        phpCAS::setVerbose(false);
        if (!phpCAS::isInitialized()) {
            phpCAS::client(
                $this->getVersion(),
                $this->getHostname(),
                $this->getPort(),
                $this->getUrl()
            );
        }

        phpCAS::setLang('CAS_Languages_Spanish');
        phpCAS::setNoCasServerValidation();
    }

    public function Authenticate() {
        phpCAS::forceAuthentication();
        if (phpCAS::getUser()) {
            return phpCAS::getUser();
        }

        return null;
    }

    public function getAttributes()
    {
        if (phpCAS::isInitialized()) {
            return phpCAS::getAttributes();
        }

        return null;
    }

    public function logout()
    {
        if ($this->isRedirectingAfterLogout()) {
            $uri = $this->getLogoutRedirect();
            phpCAS::logoutWithRedirectService($uri);
        } else {
            phpCAS::logout();
        }        
    }

    public function getHostname()
    {
        return $this->getParameter('hostname');
    }

    public function getUri()
    {
        return "https://{$this->getHostname()}:{$this->getPort()}/{$this->getUrl()}";
    }

    public function getUrl()
    {
        return $this->getParameter('url');
    }

    public function getPort()
    {
        return $this->getParameter('port');
    }

    public function getVersion()
    {
        return $this->getParameter('version');
    }

    public function isRedirectingAfterLogout()
    {
        return trim($this->getParameter('logout_redirect')) !== '';
    }

    public function getDebug()
    {
        if ($this->getParameter('debug')) {
            return $this->logFile;
        }

        return false;
    }

    public function getLogoutRedirect()
    {
        return $this->getParameter('logout_redirect');
    }

    public function getLoginFailure()
    {
        return $this->getParameter('login_failure');
    }

    private function getParameter($key)
    {
        if (!key_exists($key, $this->configuration)) {
            throw new CasException(sprintf('The %s parameter must be defined. It is missing.', $key));
        }

        return $this->configuration[$key];
    }
}
