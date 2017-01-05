<?php
//include autoload
require '../vendor/autoload.php';

$baseUri      = 'https://urlApi.com/2.0/';
$clientId     = 'client-ID';
$clientSecret = 'client-SECRET';

/**
 * Create Object token
 */
$token = new cApiConnect\jwt\Token($clientId, $clientSecret);
//Set UrL Call to retrieve the token
$token->setPath('authentication/token');
//Modify Method request (optional)
$token->setMethod("GET");
//Change the json key  of the token (optional)
$token->setTokenKeyName('myToken');
//Change the param option of request (optional)
$token->setQueryParam(
    array(
        "login" => $clientId,
        "pwd" => $clientSecret
    )
);


/**
 * Create Object Client for calls to the API
 */
$client = new cApiConnect\jwt\Client($token, $baseUri);

/**
 * Request Info Token
 */
$response = $client->request('GET', 'authentication/info');
var_dump(json_decode($response->getBody()->getContents()));


// Option request, see http://docs.guzzlephp.org/en/latest/request-options.html
/**
 * Request With query POST
 */
$response = $client->request('POST', 'customer/infos/10',
    array(
    'form_params' => [
        'offset' => 2,
        'limit' => 20
    ])
);
var_dump(json_decode($response->getBody()->getContents()));

/**
 * Request With query GET
 */
$response = $client->request('GET', 'customer/infos/10',
    array(
    'query' => [
        'offset' => 2,
        'limit' => 20
    ])
);
var_dump(json_decode($response->getBody()->getContents()));

