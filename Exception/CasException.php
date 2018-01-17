<?php
namespace STG\DEIM\Security\Bundle\CasBundle\Exception;

class CasException extends \RuntimeException
{
    protected $xmlException;
    protected $xmlDocument;

    function __construct($xmlException = null, \DOMDocument $xmlDocument = null)
    {
        $this->xmlException = $xmlException;
        $this->xmlDocument = $xmlDocument;
        parent::__construct($this->generateMessage($xmlException, $xmlDocument));
    }

    private function generateMessage($xmlException, \DOMDocument $xmlDocument)
    {
        //Parse the xml in order to show message

        if($xmlDocument) {
            $errorCode = '';
            foreach ($xmlDocument->getElementsByTagName('authenticationFailure') as $element) {
                $errorCode = $element->getAttribute('code');
            }

            switch ($errorCode) {
                case 'INVALID_TICKET':
                    return 'Ticket Inválido';
                    break;
            }
        }

        return 'Error Login';
    }

    /**
     * @return null
     */
    public function getXmlException()
    {
        return $this->xmlException;
    }

    /**
     * @param null $xmlException
     */
    public function setXmlException($xmlException)
    {
        $this->xmlException = $xmlException;
    }


}