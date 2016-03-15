# ADA Rest Api #

This folder contains all needed files to implement the _ADA Restful api_. It can safely deleted if no api access is needed in your _ADA_ installation.

## Needed external libraries ##

### Oauth2 ###
Library to implement OAuth2 server

Link                                                     | Type | Note
:--------------------------------------------------------|:----:|:----
<http://bshaffer.github.io/oauth2-server-php-docs/>      |Docs  |
<https://github.com/bshaffer/oauth2-server-php/tree/v0.9>|Code  |v0.9 used as it's the latest stable release for __PHP 5.2.x-5.3.8__

### Slim ###
Slim PHP micro framework was choosen for its routing, middleware and routing facilities

Link                             | Type | Note
:--------------------------------|:----:|:----
<http://docs.slimframework.com/> |Docs  |
<https://github.com/codeguy/Slim>|Code  |v2.4.2 used

## How do I use the ADA Api? ##

In order to use the _ADA API_ you must have an up and running version of _ADA_ platform, updated to its latest version.   
Then you must have the [apps module](https://github.com/lynxlab/ada/tree/master/modules/apps) installed and use it as an _ADA Switcher_ user to get a **client_id** and **client_secret**.  
Last, you may use these credentials to obtain an _access_token_ using the provided **OAuth2** endpoint `/token` or, you may want to check the 
[ADA PHP SDK](https://github.com/lynxlab/ada-php-sdk) for easy _PHP_ development. 
This is the preferred way and handles all the _access_token_ pains for you.

## Techincal Details ##


###.htaccess url rewrites ###
There are two levels of `.htacess` files handling url rewrites, in the following _ADA_ root subdirectories:

+ `api/`

    This file implements the `token` endpoint that is reponsible of generating a valid `access_token` to be used when executing _API_ methods that require authentication. The actual file being executed when accessing this endpoint is `tokenController.php` and the endpoint can have an **optional trailing slash** while keeping the same behaviour. Direct calls to `tokenController.php` shall produce a **404 Not Found** _HTML_ header.  
    Moreover, its is responsible of redirecting the _API_ calls made without specifing which _API_ version to use to the **latest stable _API_ version**. For instance, calls to `api/users` will be redirected to `api/v1/users` with a **301 Moved Permanently** _HTML_ header.

+ `api/v1`

    This file implements the actual _API_ calls redirection using the following rules:

    1. Every url that **does not point to an existing file** and **that does not have an extension** (such as .php) will be redirected to `index.php` without passing the `format` in the _GET_ request, thus using the default that is `json`. Example: 

        ```
        api/v1/users is rewritten to: api/v1/index.php
        ```

    2. Every url that **does not point to an existing file** and **that has an extension** (such as .php) will be redirected to `index.php` passing the `format` in the _GET_ request, thus using the extension guessed output format. Example: 

        ```
        api/v1/users.xml is rewritten to: api/v1/index.php?format=xml
        ```

###### Supported formats are: **json**, **xml** and **php**(outputs a php serialized array). Passing an unsupported format will generate a **400 Bad Request** _HTML_ header.
