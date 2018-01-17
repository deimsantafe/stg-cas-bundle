<?php

namespace STG\DEIM\Security\Bundle\CasBundle\Lib;

use STG\DEIM\Security\Bundle\CasBundle\Exception\CasException;
use Symfony\Component\Debug\Exception\ContextErrorException;

class Client
{
    /**
     * @var CAS
     */
    protected $cas;

    /**
     * @param CAS $cas
     */
    public function __construct(CAS $cas)
    {
        $this->cas = $cas;
    }

    /**
     * Obtenir un service ticket pour un service proxifié
     *
     * @param $service
     * @param $pgt
     * @return string
     * @throws \Exception
     */
    public function getServiceTicket($service, $pgt)
    {
        if (!$this->cas->isProxy()) {
            throw new \Exception('You can a service token only in proxy mode');
        }

        $curl = $this->getCurl($this->cas->getProxyServiceUrl($service, $pgt));
        $curlResponse = curl_exec($curl);
        if (!$curlResponse) {
            throw new \Exception("Error in CAS Validation Request : " . curl_error($curl));
        }

        $document = new \DOMDocument();
        $document->loadXML($curlResponse);
        if (!$document->getElementsByTagName('proxyTicket')->length) {
            throw new \Exception("No proxy ticket found in validation request");
        }

        return $document->getElementsByTagName('proxyTicket')->item(0)->textContent;
    }

    /**
     * Valider le service ticket obtenu et retourner le résultat de la requête de validation
     *
     * @param $service
     * @param $ticket
     * @param null $callback
     * @return mixed
     * @throws \Exception
     */
    public function validateServiceTicket($service, $ticket, $callback = null)
    {
        if (!$this->cas->isProxy()) {
            $validationUrl = $this->cas->getValidationUrl($service, $ticket);
        } else {
            if (is_null($callback)) {
                throw new \Exception('A callback url is needed fro proxy');
            }
            $validationUrl = $this->cas->getProxyValidationUrl($service, $ticket, $callback);
        }


        $curl = $this->getCurl($validationUrl);
        $curlResponse = curl_exec($curl);

        if (!$curlResponse) {
            throw new \Exception("Error in CAS Validation Request : " . curl_error($curl));
        }

        $document = new \DOMDocument();
        $document->loadXML($curlResponse);

        $attributes = $document->getElementsByTagName('attributes')->item(0);
        $result['attributes'] = array();

        if (!is_object($attributes)) {
            throw new CasException($document->saveXML(), $document);
        }
        try {
            foreach ($attributes->childNodes as $att) {
                $result['attributes'][$att->nodeName] = $att->nodeValue;
            }

            $result['user'] = $document->getElementsByTagName($this->cas->getUsernameAttribute())->item(0)->textContent;

            if ($this->cas->isProxy()) {
                if (!$document->getElementsByTagName('proxyGrantingTicket')->length) {
                    throw new \Exception("No proxy ticket found in validation request");
                }
                $result['pgtiou'] = $document->getElementsByTagName('proxyGrantingTicket')->item(0)->textContent;
            }

            return $result;

        } catch (\Exception $ex) {
            throw new CasException('No pudo loguearse en CAS', null);
        }
    }

    /**
     * @param $url
     * @return resource
     */
    protected function getCurl($url)
    {
        if ($cert = $this->cas->getCert()) {
            $options = array(
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FOLLOWLOCATION => 0,   // no puede estar habilitado junto al open_basedir
                CURLOPT_CAINFO => $this->cas->getCert()
            );
        } else {
            $options = array(
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FOLLOWLOCATION => 0  // no puede estar habilitado junto al open_basedir
            );
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, $options);

        return $curl;
    }
}
