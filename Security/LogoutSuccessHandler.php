<?php

namespace STG\DEIM\Security\Bundle\CasBundle\Security;

use STG\DEIM\Security\Bundle\CasBundle\Lib\CAS;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;

use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;

class LogoutSuccessHandler extends DefaultLogoutSuccessHandler 
{
    /**
     * @var \STG\DEIM\Security\Bundle\CasBundle\Lib\CAS
     */
    protected $cas;

    /**
     * @param CAS $cas
     */
    public function __construct(HttpUtils $httpUtils, $targetUrl = '/', CAS $cas)
    {
        $this->httpUtils = $httpUtils;
        $this->targetUrl = $targetUrl;
        $this->cas = $cas;
    }

    public function onLogoutSuccess(Request $request)
    {
        return new RedirectResponse($this->cas->getLogoutUrl($this->httpUtils->generateUri($request, $this->targetUrl)));
    }
}
