##
## CLIENT API With JSONP
##

The api-client class simplifies the connection to a remote server (API) using the Guzzle library. 
Currently the supported authentication method is JWT (JSON Web Token)

## Requirements

* Composer (https://getcomposer.org)
* packagist : [packagwcd/client-api-connect](https://packagist.webcd.fr/packages/packagwcd/client-api-connect)

```json
{
	"require"		: {
        "packagwcd/client-api-connect" : "~1.0"
	},
	"repositories": [ { "type": "composer", "url": "https://packagist.webcd.fr/" } ],
    "require-dependencies": "true",
}
```
Ne pas oublier d'ajouter la ligne "repositories" et "require-dependencies" dans votre composer


## Features

* HTTP Client : [Guzzle](https://github.com/guzzle/guzzle)
* HTTP Client Cache : [Guzzle cache](https://github.com/Kevinrob/guzzle-cache-middleware)
* JSON Web Token : [php-jwt](https://github.com/firebase/php-jwt)

## Installation

1. Fork the project or add composer package
2. Launch `composer install`