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
     * Header Cache Guzzle
     * @var GuzzleHttp\HandlerStack
     */
    protected $stackCache;

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
    public function __construct(\cApiConnect\jwt\Token $token, $baseUri = '', $options = [])
    {
        $defaultOption = [
            'base_uri' => $baseUri,
            'timeout' => '2.0',
            'exceptions' => false,
        ];

        //Handler Cache
        if ($this->stackCache != null) {
            $defaultOption['handler'] = $this->stackCache;
        }

        $optionsGuzzle      = array_replace_recursive($defaultOption, $options);
        $this->clientGuzzle = new GuzzleClient($optionsGuzzle);

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
        $token = $this->generateToken();

        //Add header Token
        $headerToken = [
            'headers' => [
                'Authorization' => sprintf($this->headerAuhtorize.' %s', $token),
            ]
        ];
        $options     = array_replace_recursive($options, $headerToken);

        try {
            return $this->clientGuzzle->request($method, $uri, $options);
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
     * Return the Token object
     * @return \cApiConnect\jwt\Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Return The guzzle client object
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->clientGuzzle;
    }

    /**
     * Generates the token, if it does not exist or has expired
     * @return string   Token signature
     */
    public function generateToken()
    {
        $token = $this->getToken();

        //check if token expired
        if ($token->isTokenExpired() == true) {

            $methodCall = $token->getMethod();
            $uriCall    = $token->getPath();
            $queryCall  = $token->getParamQuery();

            if (empty($uriCall) === false or is_null($uriCall) == false) {
                $responseKey = $this->getClient()->request(
                    $methodCall, $uriCall, ['form_params' => $queryCall]
                );
                $dataToken   = json_decode($responseKey->getBody(), true);
                $token->setTokenSignature($dataToken[$token->getTokenKeyName()]);
            } else {
                trigger_error('The url for the token generation is not defined', E_USER_ERROR);
            }
        }
        return $token->getTokenSignature();
    }

    /**
     * Activate Cache Request
     * @param Str $folderCache
     * @return GuzzleHttp\HandlerStack
     */
    public function activateCache($folderCache)
    {
        if ($this->stackCache == null) {
            //Create folder
            if (is_dir($folderCache) == false) {
                mkdir($folderCache, 0777, true);
                chmod($folderCache, 0777);
            }

            $localStorage  = new Local($folderCache);
            $systemStorage = new FlysystemStorage($localStorage);
            $cacheStrategy = new PrivateCacheStrategy($systemStorage);
            $cache         = new CacheMiddleware($cacheStrategy);

            $stack            = HandlerStack::create();
            $this->stackCache = $stack->push($cache);
        }

        return $this->stackCache;
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
}