<?php

namespace STG\DEIM\Security\Bundle\CasBundle\Lib;

use STG\DEIM\Security\Bundle\CasBundle\Exception\CasException;

class Client
{
    public function __construct(private readonly CAS $cas) {}

    /**
     * Obtiene un service ticket para un servicio proxificado (sólo modo proxy).
     *
     * @throws \Exception
     */
    public function getServiceTicket(string $service, string $pgt): string
    {
        if (!$this->cas->isProxy()) {
            throw new \Exception('getServiceTicket() sólo es válido en modo proxy');
        }

        $curl         = $this->getCurl($this->cas->getProxyServiceUrl($service, $pgt));
        $curlResponse = curl_exec($curl);

        if (!$curlResponse) {
            throw new \Exception('Error en la petición CAS: ' . curl_error($curl));
        }

        $document = new \DOMDocument();
        $document->loadXML($curlResponse);

        if (!$document->getElementsByTagName('proxyTicket')->length) {
            throw new \Exception('No se encontró proxyTicket en la respuesta CAS');
        }

        return $document->getElementsByTagName('proxyTicket')->item(0)->textContent;
    }

    /**
     * Valida el service ticket contra el servidor CAS.
     *
     * @return array{user: string, attributes: array<string, string>, pgtiou?: string}
     * @throws \Exception|CasException
     */
    public function validateServiceTicket(string $service, string $ticket, ?string $callback = null): array
    {
        $validationUrl = $this->cas->isProxy()
            ? $this->cas->getProxyValidationUrl($service, $ticket, $callback ?? throw new \Exception('Se requiere callback en modo proxy'))
            : $this->cas->getValidationUrl($service, $ticket);

        $curl         = $this->getCurl($validationUrl);
        $curlResponse = curl_exec($curl);

        if (!$curlResponse) {
            throw new \Exception('Error en la petición de validación CAS: ' . curl_error($curl));
        }

        $document = new \DOMDocument();
        $document->loadXML($curlResponse);

        $attributes = $document->getElementsByTagName('attributes')->item(0);

        if (!$attributes instanceof \DOMElement) {
            throw new CasException($document->saveXML(), $document);
        }

        try {
            $result = ['attributes' => []];

            foreach ($attributes->childNodes as $node) {
                if ($node->nodeType === XML_ELEMENT_NODE) {
                    $result['attributes'][$node->nodeName] = $node->nodeValue;
                }
            }

            $usernameNode = $document->getElementsByTagName($this->cas->getUsernameAttribute())->item(0);
            if ($usernameNode === null) {
                throw new CasException('Atributo de usuario no encontrado en respuesta CAS', null);
            }

            $result['user'] = $usernameNode->textContent;

            if ($this->cas->isProxy()) {
                $pgtNode = $document->getElementsByTagName('proxyGrantingTicket')->item(0);
                if ($pgtNode === null) {
                    throw new \Exception('No se encontró proxyGrantingTicket en la respuesta');
                }
                $result['pgtiou'] = $pgtNode->textContent;
            }

            return $result;

        } catch (CasException $e) {
            throw $e;
        } catch (\Exception) {
            throw new CasException('No pudo loguearse en CAS', null);
        }
    }

    /**
     * @return \CurlHandle
     */
    protected function getCurl(string $url): \CurlHandle
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ];

        if ($cert = $this->cas->getCert()) {
            $options[CURLOPT_SSL_VERIFYPEER] = true;
            $options[CURLOPT_CAINFO]         = $cert;
        } else {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, $options);

        return $curl;
    }
}
