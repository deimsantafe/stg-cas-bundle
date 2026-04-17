<?php

namespace STG\DEIM\Security\Bundle\CasBundle\Security;

use STG\DEIM\Security\Bundle\CasBundle\Lib\CAS;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Entry point standalone (opcional).
 *
 * En el flujo normal el propio CasAuthenticator actúa como entry point
 * (implementa AuthenticationEntryPointInterface directamente).
 *
 * Esta clase queda disponible para casos donde se necesite registrarla
 * de forma independiente vía entry_point: en security.yaml.
 */
class CasAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly CAS $cas,
        private readonly HttpUtils $httpUtils,
        private readonly string $checkPath,
    ) {}

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $serviceUrl = $this->httpUtils->generateUri($request, $this->checkPath);

        return new RedirectResponse($this->cas->getLoginUrl($serviceUrl));
    }
}
