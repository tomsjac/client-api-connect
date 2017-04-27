<?php
//include autoload
require '../vendor/autoload.php';

$baseUri      = 'https://urlApi.com/2.0/';
$clientId     = 'client-ID';
$clientSecret = 'client-SECRET';

//IF UP FILE
if (!empty($_FILES)) {

    //Create instance Token
    $token  = new cApiConnect\jwt\Token($clientId, $clientSecret);
    $token->setPath('authentication/token');

    //Create instance Client
    $client       = new cApiConnect\jwt\Client($token, $baseUri, array('timeout' => 36000));

    /*
     * This is a POST shipment, so you should use form_params and not query.
     *  However, Guzzle does not allow to send a form_params and a multipart at the same time, hence the passage of additional parameters with query.
     *  (See http://docs.guzzlephp.org/en/latest/request-options.html#multipart)
     */
    $response = $client->request('POST', 'documents/add',
        array(
        'query' => [
            'dataId' => $_POST['dataId'],
            'nameFile' => 'superFile'
        ],
        'multipart' => [
            [
                'contents' => $_FILES["file"]['tmp_name'],
                'filename' => $_FILES["file"]['name']
            ]
        ]
        )
    );
    $response = ($response->getBody()->getContents());
    var_dump($response);
}
?>
<!-- FORM -->
<html>
    <body>
        <form action="" enctype="multipart/form-data" method="post">
            <p>
                <input name="file" type="file" />
            </p>
            <p>
                <input name="send" type="submit" value="Send" />
            </p>
        </form>
    </body>
</html>
