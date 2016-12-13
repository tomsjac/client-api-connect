<?php
namespace cApiConnect\jwt;

/**
 * Managing Token for calls
 * @author thomas
 */
use \Firebase\JWT\JWT;

class Token
{
    /**
     * @var string
     */
    protected $tokenKeyName = 'token';

    /**
     * @var string/int
     */
    protected $clientId;

    /**
     * @var string/int
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $methodCall = 'POST';

    /**
     * @var string
     */
    protected $urlCall;

    /**
     * @var array
     */
    protected $queryParam;

    /**
     * @var string
     */
    protected $tokenSignature;

    /**
     * @var int
     */
    protected $timeExpire;

    /**
     * Construct
     * @param str $clientId
     * @param str $clientSecret
     */
    public function __construct($clientId, $clientSecret)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * Set Call method, default : POST
     * @param str $method   (POST, GET)
     */
    public function setMethod($method)
    {
        $this->methodCall = $method;
    }

    /**
     * Set Url call to generate the token
     * @param str $path
     */
    public function setPath($path)
    {
        $this->urlCall = $path;
    }

    /**
     * Set query settings for call
     * @param array $array
     */
    public function setQueryParam($array)
    {
        $this->queryParam = $array;
    }

    /**
     * Set the signature token
     * @param str $signature
     */
    public function setTokenSignature($signature)
    {
        $this->tokenSignature = $signature;
        //Collect information token to the expiration date
        $dataToken        = $this->readDataToken();
        if ($dataToken != null) {
            $this->timeExpire = $dataToken['exp'];
        }
    }

    /**
     * Set the key name token of the response Json, default : token
     * @param str $nameKey
     */
    public function setTokenKeyName($nameKey)
    {
        $this->tokenKeyName = $nameKey;
    }

    /**
     * Return the signature token
     * @return str
     */
    public function getTokenSignature()
    {
        return $this->tokenSignature;
    }

    /**
     * Return the key name token of the response Json
     * @return str
     */
    public function getTokenKeyName()
    {
        return $this->tokenKeyName;
    }

    /**
     * Return the call method
     * @return str
     */
    public function getMethod()
    {
        return $this->methodCall;
    }

    /**
     * Return Url call to generate the token
     * @return str
     */
    public function getPath()
    {
        return $this->urlCall;
    }

    /**
     * Checked if the token is not expired
     * @return Bool
     */
    public function isTokenExpired()
    {
        return $this->timeExpire < strtotime("now");
    }

    /**
     * Return query settings for call, Default : ['clientId' => ..., 'clientSecret' => ...]
     * @return type
     */
    public function getParamQuery()
    {
        if ($this->queryParam != null) {
            return $this->queryParam;
        }
        return ['clientId' => $this->clientId, 'clientSecret' => $this->clientSecret];
    }

    /**
     * Return the content of the decoded token
     * @return str
     */
    protected function readDataToken()
    {
        if ($this->getTokenSignature() != null) {
            list($headb64, $bodyb64, $cryptob64) = explode('.', $this->getTokenSignature());
            $contentDecode = \Firebase\JWT\JWT::urlsafeB64Decode($bodyb64);
            return json_decode($contentDecode, true);
        }
        return null;
    }
}