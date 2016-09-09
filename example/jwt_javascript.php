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
 * And genere token
 */
$client = new cApiConnect\jwt\Client($token, $baseUri);
$token  = $client->generateToken();

/**
 * A little elsewhere, in your javascript file and template
 */
echo "
    <script>
        //Ajax Jquery
        var token = {{token}};
        $.ajax({
            url: 'https://urlApi.com/2.0/hello',
            crossDomain: true,
            dataType: 'json',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer '+token,
            },
        error : function() {
            // error handler
        },
        success: function(data) {
            console.log(data.response);
        }
    });
    </script>
";
