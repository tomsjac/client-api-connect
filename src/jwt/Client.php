<?php

namespace cApiConnect\jwt;

/**
 * Managing calls to the API
 * @author thomas
 */
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use League\Flysystem\Adapter\Local;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Kevinrob\GuzzleCache\Storage\FlysystemStorage;

class Client
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $clientGuzzle;

    /**
     * Cache
     * @var \cApiConnect\cache\ICache
     */
    protected $cache;

    /**
     * Options Guzzle
     * @var Array
     */
    protected $options = [];

    /**
     * @var \jwtClient\Token
     */
    protected $token;

    /**
     * @var string
     */
    protected $headerAuhtorize = 'Bearer';

    /**
     * Construct
     * @param \jwtClient\Token $token
     * @param str $baseUri  base url call the api
     * @param Array $options  Option For guzzle : http://docs.guzzlephp.org/en/latest/request-options.html
     */
    public function __construct(\cApiConnect\jwt\Token $token = null, $baseUri = '', $options = [])
    {
        //Default Option
        $this->addOptionClient([
            'base_uri' => $baseUri,
            'timeout' => '2.0',
            'exceptions' => false,
            'headers' => [
                'Pragma' => 'no-cache',
            ]
        ]);

        //Add option User
        $this->addOptionClient($options);
        $this->token = $token;
    }

    /**
     * Call to the API : Class use Guzzle
     * @param str $method   Type Method (GET, POST ...)
     * @param str $uri     Url call
     * @param Array $options    Option, see http://docs.guzzlephp.org/en/latest/request-options.html
     * @return \GuzzleHttp\Psr7\Response
     */
    public function request($method, $uri, $options = array())
    {
        $guzzle = $this->initClient();
        $token  = $this->generateToken();

        //Add header Token
        $headerToken = [
            'headers' => [
                'Authorization' => sprintf($this->headerAuhtorize.' %s', $token),
            ]
        ];
        $options     = array_replace_recursive($options, $headerToken);

        try {
            return $guzzle->request($method, $uri, $options);
        } catch (ClientException $e) {
            return $this->getError($e);
        }
    }

    /**
     * Set the token name of the header Authorization, Default : Bearer
     * @param string $txt
     */
    public function setHeaderAuthorize($txt)
    {
        $this->headerAuhtorize = $txt;
    }

    /**
     * Add Option Client With the data Default
     * @param Array $data Option
     * @return boolean
     */
    public function addOptionClient($data)
    {
        $this->options = array_replace_recursive($this->options, $data);
        return true;
    }

    /**
     * Return the Token object
     * @return \cApiConnect\jwt\Token
     */
    public function getToken()
    {
        if (is_null($this->cache) === false) {
            $fileCache = $this->token->getCacheName();
            $this->token->setTokenSignature($this->cache->getHandler()->read($fileCache));
        }
        return $this->token;
    }

    /**
     * Set un nouveau token à l'api client
     * @param \cApiConnect\jwt\Token $token
     */
    public function setNewToken(\cApiConnect\jwt\Token $token)
    {
        $this->token = $token;

        if (is_null($this->cache) === false) {
            //Generate Token Name Cache
            $this->token->setCacheName($this->getHashNameToken());
        }
    }

    /**
     * Return The guzzle client object
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->initClient();
    }

    /**
     * Generates the token, if it does not exist or has expired
     * @return string   Token signature
     */
    public function generateToken()
    {
        $token = $this->getToken();

        if ($token == null) {
            trigger_error('No token declared, Use setNewToken', E_USER_ERROR);
        }

        //check if token expired
        if ($token->isTokenExpired() == true) {
            $methodCall = $token->getMethod();
            $uriCall    = $token->getPath();
            $queryCall  = $token->getParamQuery();

            if (empty($uriCall) === false or is_null($uriCall) == false) {
                $responseKey = $this->getClient()->request(
                    $methodCall, $uriCall, ['form_params' => $queryCall]
                );

                $this->saveToken(json_decode($responseKey->getBody(), true));
            } else {
                trigger_error('The url for the token generation is not defined', E_USER_ERROR);
            }
        }
        return $token->getTokenSignature();
    }

    /**
     * Activate Cache Request
     * @param Str $folderCache  Folder Save File cache
     * @param Int $time     Time in second of reloading of the cache
     * @param Bool $tokenOnly   Cache only token
     * @return GuzzleHttp\HandlerStack
     */
    public function activateCache($folderCache, $time = 60, $tokenOnly = false)
    {
        $this->cache = new \cApiConnect\cache\Cache('cacheFile', $time);
        $stack       = $this->cache->getHandler();
        $stack->setFolder($folderCache);

        if ($tokenOnly == false) {
            //Add handler Option guzzle
            $this->addOptionClient(['handler' => $stack->getStack()]);

            //Add Header Control Cache
            $this->addOptionClient(['headers' => $this->cache->getHeaderCache()]);
        }

        //Generate Token Name Cache
        if (is_null($this->token) == false) {
            $this->token->setCacheName($this->getHashNameToken());
        }
    }

    /**
     * Save token data, data from API call
     * @param Array $data
     */
    protected function saveToken($data)
    {
        $dataToken = $data[$this->token->getTokenKeyName()];
        $this->token->setTokenSignature($dataToken);

        if (is_null($this->cache) === false) {
            $fileCache = $this->token->getCacheName();
            $this->cache->getHandler()->write($fileCache, $dataToken);
        }
    }

    /**
     * Init Guzzle
     * @param Bool $force   Force to the generated
     */
    protected function initClient($force = false)
    {
        if ($this->clientGuzzle == null or $force == true) {
            $this->clientGuzzle = new GuzzleClient($this->options);
        }
        return $this->clientGuzzle;
    }

    /**
     * Return Error code for the call to the API
     * @param ClientException $e
     * @return string
     */
    protected function getError(ClientException $e)
    {
        $error = '';
        $error += Psr7\str($e->getRequest());
        $error += Psr7\str($e->getResponse());

        return $error;
    }

    /**
     * Retourne le hash pour le token à mettre en cache
     * @return string
     */
    protected function getHashNameToken()
    {
        return hash('ripemd160', $this->token->getClientId()).'.jwt';
    }
}