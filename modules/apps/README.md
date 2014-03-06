# ADA Client ID and Secret Generation (aka APPS)  Module #
----------------------------------------------

This module is used by the swithcer user to get its own appID and secret to be used for _API_ authentication via the _OAuth2_ protocol.

At the time of writing only a button is displayed on the index page, and when the user clicks it the module will:

1. Generate an appID and secret.
2. Check if the user id of the logged switcher already has an associated appID/secret pair.
3. If it has, return it or else save the pair generated at point 1 for the logged user id and return it.

## How to use the generated appID/secret pair? ##

The _ADA SDK_ will handle the generation of a valid **access token**, but if you want to get one by yourself you must make a request to the `token` _API_ access point passing the appID and secret.

A *curl* example is:

**using HTTP Basic Authentication**

```bash
$ curl -u TestClient:TestSecret https://ada.lynxlab.com/api/token -d 'grant_type=client_credentials'
```

**using a POST request**

```bash
$ curl https://ada.lynxlab.com/api/token -d 'grant_type=client_credentials&client_id=TestClient&client_secret=TestSecret'
```

This is it for this module, further documentation is in the _ADA API_ folder.