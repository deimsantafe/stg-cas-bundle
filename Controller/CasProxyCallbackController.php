<?php

namespace STG\DEIM\Security\Bundle\CasBundle\Controller;

use STG\DEIM\Security\Bundle\CasBundle\Lib\Storage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controlador del back-channel CAS proxy (PGT callback).
 *
 * El servidor CAS llama a esta URL para entregar el PGT (Proxy Granting Ticket).
 * Solo es necesario cuando proxy: true en la configuración del bundle.
 *
 * Ruta sugerida: /cas/proxy-callback (definirla en routes.yaml de la aplicación).
 */
class CasProxyCallbackController
{
    public function __construct(private readonly Storage $storage) {}

    public function callback(Request $request): Response
    {
        $pgtIou = $request->query->get('pgtIou');
        $pgtId  = $request->query->get('pgtId');

        if ($pgtIou !== null && $pgtId !== null) {
            $this->storage->addPgt($pgtId, $pgtIou);
        }

        return new Response('done', Response::HTTP_OK, ['Content-Length' => '4']);
    }
}
