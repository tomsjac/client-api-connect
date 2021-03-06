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

/**
 * Create Object Client for calls to the API
 */
$client = new cApiConnect\jwt\Client($token, $baseUri);
$client->activateCache('/path/to/cache');

/**
 * Request Info Token
 */
$response = $client->request('GET', 'authentication/info');
var_dump(json_decode($response->getBody()->getContents()));

/**
 * other Request
 */
$response = $client->request('GET', 'custonner/infos/10');
var_dump(json_decode($response->getBody()->getContents()));

