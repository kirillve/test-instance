<?php
/**
 * Created by PhpStorm.
 * User: kvereshchagin
 * Date: 2018-05-15
 * Time: 7:13 AM
 */

namespace App\Base;

use InvalidArgumentException;

/**
 * Class Response
 * @package App\Base
 */
class Response
{
    /**
     * @var array
     */
    public static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );
    /**
     * @var string
     */
    public $version;
    /**
     * @var int
     */
    protected $statusCode = 200;
    /**
     * @var string
     */
    protected $statusText;
    /**
     * @var array
     */
    protected $parameters = array();
    /**
     * @var array
     */
    protected $httpHeaders = array();
    /**
     * @var string
     */
    protected $responseFormat = '';
    /**
     * @var array
     */
    protected $responseFields = '';

    /**
     * Response constructor.
     * @param array $parameters
     * @param int $statusCode
     * @param array $headers
     * @throws InvalidArgumentException
     */
    public function __construct($parameters = array(), $statusCode = 200, $headers = array())
    {
        $this->setParameters($parameters);
        $this->setStatusCode($statusCode);
        $this->setHttpHeaders($headers);
        $this->version = '1.1';
    }

    /**
     * Converts the response object to string containing all headers and the response content.
     *
     * @return string The response with headers and content
     */
    public function __toString()
    {
        $headers = array();
        foreach ($this->httpHeaders as $name => $value) {
            $headers[$name] = (array)$value;
        }

        return sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText) . "\r\n" .
            $this->getHttpHeadersAsString($headers) . "\r\n" .
            $this->getResponseBody();
    }

    /**
     * Function from Symfony2 HttpFoundation - output pretty header
     *
     * @param array $headers
     * @return string
     */
    private function getHttpHeadersAsString($headers)
    {
        if (count($headers) == 0) {
            return '';
        }

        $max = max(array_map('strlen', array_keys($headers))) + 1;
        $content = '';
        ksort($headers);
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $content .= sprintf("%-{$max}s %s\r\n", $this->beautifyHeaderName($name) . ':', $value);
            }
        }

        return $content;
    }

    /**
     * Function from Symfony2 HttpFoundation - output pretty header
     *
     * @param string $name
     * @return mixed
     */
    private function beautifyHeaderName($name)
    {
        return preg_replace_callback('/\-(.)/', array($this, 'beautifyCallback'), ucfirst($name));
    }

    /**
     * @return null|string
     */
    public function getResponseBody()
    {
        $response = '';
        
        if(!empty($this->responseFields)){
           $this->parameters = $this->filterResponseFields($this->parameters,$this->responseFields);
        }

        switch ($this->responseFormat) {
            case 'text':
                if (is_array($this->parameters)) {
                    $response = "Status:".$this->getStatusCode().", ".$this->responseFormatText($this->parameters);
                }
                break;
            case 'json':
                $response = json_encode(array(
                    'status'=>$this->getStatusCode(), 
                    'body'=>$this->parameters
                ));
                break;
            case 'xml':
                $xmlUserInfo = new \SimpleXMLElement("<?xml version=\"1.0\"?><root></root>");
                $this->responseFormatXml(array(
                    'status'=>$this->getStatusCode(),
                    'body'=>array(
                        'user_info'=>$this->parameters
                    )
                ),$xmlUserInfo);
                $response = $xmlUserInfo->asXML();                 
                break;            
            default:
                if (is_array($this->parameters)) {
                    $response = "Status:".$this->getStatusCode().", ".$this->responseFormatText($this->parameters);
                }
                break;
        }

        return $response;
    }


    /**
     * Function to filter response fields
     *
     * @param array $responseData
     * @param array $responseFields
     * @return array
     */
    public function filterResponseFields($responseData,$responseFields, &$specialItems = [])
    {
        if(!empty($responseData)) {
            $data = [];

            foreach($responseData as $key => $val) { 
                if(is_array($val)) {
                    $this->filterResponseFields($val,$responseFields, $specialItems);
                }
                else{
                    if(in_array($key,$responseFields)) {
                        $data[$key] = $val;
                    }
                }
            }  
            
            if(!empty($data)){
                $specialItems[] = $data;
            }
        }

        return $specialItems;
    }


    /**
     * Function to response formate text
     *
     * @param array $responseData
     * @return array
     */
    public function responseFormatText($responseData,&$response = '') 
    {
        foreach($responseData as $key => $value) {
            if(is_array($value)) {
                $response .= PHP_EOL.PHP_EOL.PHP_EOL;
                $this->responseFormatText($value, $response);
            }else {
                $response .= $key . ' ==> ' . $value .PHP_EOL;
            }
        }
        return $response;
    }


    /**
     * Function to response formate xml
     *
     * @param array $responseData
     * @return array
     */
    public function responseFormatXml($responseData, &$xmlUserInfo) {
        foreach($responseData as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subNode = $xmlUserInfo->addChild("$key");
                    $this->responseFormatXml($value, $subNode);
                }else{
                    $subNode = $xmlUserInfo->addChild("item$key");
                    $this->responseFormatXml($value, $subNode);
                }
            }else {
                $xmlUserInfo->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     * @param string $text
     * @throws InvalidArgumentException
     */
    public function setStatusCode($statusCode, $text = null)
    {
        $this->statusCode = (int)$statusCode;
        if ($this->isInvalid()) {
            throw new InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $statusCode));
        }

        $this->statusText = false === $text ? '' : (null === $text ? self::$statusTexts[$this->statusCode] : $text);
    }

    /**
     * @return string
     */
    public function getStatusText()
    {
        return $this->statusText;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getResponseFormat()
    {
        return $this->responseFormat;
    }

    /**
     * @param string $responseFormat
     */
    public function setResponseFormat($responseFormat)
    {
        $this->responseFormat = $responseFormat;
    }

    /**
     * @return array
     */
    public function getResponseFields()
    {
        return $this->responseFields;
    }

    /**
     * @param array $responseFields
     */
    public function setResponseFields($responseFields)
    {
        $this->responseFields = $responseFields;
    }


    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getParameter($name, $default = null)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getHttpHeader($name, $default = null)
    {
        return isset($this->httpHeaders[$name]) ? $this->httpHeaders[$name] : $default;
    }

    /**
     *
     */
    public function send()
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            return;
        }
        $this->setHttpHeader('Content-Type', 'text/html');
        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText));

        foreach ($this->getHttpHeaders() as $name => $header) {
            header(sprintf('%s: %s', $name, $header));
        }
        echo $this->getResponseBody();
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setHttpHeader($name, $value)
    {
        $this->httpHeaders[$name] = $value;
    }

    /**
     * @return array
     */
    public function getHttpHeaders()
    {
        return $this->httpHeaders;
    }

    /**
     * @param array $httpHeaders
     */
    public function setHttpHeaders(array $httpHeaders)
    {
        $this->httpHeaders = $httpHeaders;
    }

    /**
     * @param int $statusCode
     * @param string $url
     * @param string $state
     * @param string $error
     * @param string $errorDescription
     * @param string $errorUri
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function setRedirect(
        $statusCode,
        $url,
        $state = null,
        $error = null,
        $errorDescription = null,
        $errorUri = null
    ) {
        if (empty($url)) {
            throw new InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $parameters = array();

        if (!is_null($state)) {
            $parameters['state'] = $state;
        }

        if (!is_null($error)) {
            $this->setError(400, $error, $errorDescription, $errorUri);
        }
        $this->setStatusCode($statusCode);
        $this->addParameters($parameters);

        if (count($this->parameters) > 0) {
            // add parameters to URL redirection
            $parts = parse_url($url);
            $sep = isset($parts['query']) && count($parts['query']) > 0 ? '&' : '?';
            $url .= $sep . http_build_query($this->parameters);
        }

        $this->addHttpHeaders(array('Location' => $url));

        if (!$this->isRedirection()) {
            throw new InvalidArgumentException(sprintf(
                'The HTTP status code is not a redirect ("%s" given).',
                $statusCode
            ));
        }
    }

    /**
     * @param int $statusCode
     * @param string $error
     * @param string $errorDescription
     * @param string $errorUri
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function setError($statusCode, $error, $errorDescription = null, $errorUri = null)
    {
        $parameters = array(
            'error' => $error,
            'error_description' => $errorDescription,
        );

        if (!is_null($errorUri)) {
            if (strlen($errorUri) > 0 && $errorUri[0] == '#') {
                // we are referencing an oauth bookmark (for brevity)
                $errorUri = 'http://tools.ietf.org/html/rfc6749' . $errorUri;
            }
            $parameters['error_uri'] = $errorUri;
        }

        $httpHeaders = array(
            'Cache-Control' => 'no-store'
        );

        $this->setStatusCode($statusCode);
        $this->addParameters($parameters);
        $this->addHttpHeaders($httpHeaders);

        if (!$this->isClientError() && !$this->isServerError()) {
            throw new InvalidArgumentException(sprintf(
                'The HTTP status code is not an error ("%s" given).',
                $statusCode
            ));
        }
    }

    /**
     * @param array $parameters
     */
    public function addParameters(array $parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    /**
     * @param array $httpHeaders
     */
    public function addHttpHeaders(array $httpHeaders)
    {
        $this->httpHeaders = array_merge($this->httpHeaders, $httpHeaders);
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isClientError()
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isServerError()
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isRedirection()
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * @return Boolean
     *
     * @api
     *
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     */
    public function isInvalid()
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isInformational()
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isSuccessful()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Returns the build header line.
     *
     * @param string $name The header name
     * @param string $value The header value
     *
     * @return string The built header line
     */
    protected function buildHeader($name, $value)
    {
        return sprintf("%s: %s\n", $name, $value);
    }

    /**
     * Function from Symfony2 HttpFoundation - output pretty header
     *
     * @param array $match
     * @return string
     */
    private function beautifyCallback($match)
    {
        return '-' . strtoupper($match[1]);
    }
}
