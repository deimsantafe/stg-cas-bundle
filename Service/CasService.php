<?php

namespace Stg\Bundle\CasBundle\Service;

use Psr\Log\LoggerInterface;
use Stg\Bundle\CasBundle\Exception\CasException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use phpCAS;

class CasService
{
    private $configuration;
    private $logger;
    private $router;

    public function __construct(
                        array $configuration,
                        LoggerInterface $logger,
                        RouterInterface $router)
    {
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->router = $router;
    }

    protected function initPhpCas()
    {
        phpCAS::setLogger($this->getDebug());
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
        $this->initPhpCas();
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

    public function logout($request)
    {
        $this->initPhpCas();
        if ($this->isRedirectingAfterLogout()) {
            $uri = $this->generateUrlAbsolute($request, $this->getParameter('logout_redirect'));
            phpCAS::logoutWithRedirectService($uri);
        } else {
            phpCAS::logout();
        }        
    }

    public function loginFailure(Request $request, AuthenticationException $exception)
    {
        $this->initPhpCas();

        if ($this->isRedirectingAfterFailure()) {
            $uri = $this->generateUrlAbsolute($request, $this->getParameter('login_failure'));
            phpCAS::logoutWithRedirectService($uri);
        } 
        else {
            phpCAS::log($exception->getMessage());
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

    public function isRedirectingAfterFailure()
    {
        return trim($this->getParameter('login_failure')) !== '';
    }

    public function getDebug(): ?LoggerInterface
    {
        if ($this->getParameter('debug')) {
            return $this->logger;
        }

        return null;
    }

    public function getLogoutRedirect($request)
    {
        return $this->generateUrlAbsolute($request, $this->getParameter('logout_redirect'));
    }

    public function getLoginFailure()
    {
        return $this->getParameter('login_failure');
    }

    private function generateUrlAbsolute($request, $route) {
        $scheme = $request->getScheme();
        if ($host = $request->headers->get('x-forwarded-host')) {
            $uri = $scheme . '://' . $host . $this->router->generate($route);
        }
        else {    
            $uri = $this->router->generate(
                $route,
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }  
        return $uri;  
    }
    
    private function getParameter($key)
    {
        if (!key_exists($key, $this->configuration)) {
            throw new CasException(sprintf('The %s parameter must be defined. It is missing.', $key));
        }

        return $this->configuration[$key];
    }
}
