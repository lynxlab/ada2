# Zoom integration with ADA

Copy the provide config file ```comunica/include/ZoomConf.config_DEFAULT.inc.php``` to ```comunica/include/ZoomConf.config.inc.php```

Two zoom app are needed to integrate with ADA:

1. Meeting SDK

    Is needed for the zoom web frontend integration inside ADA nodes and pages.
Please configure a Meeting SDK type app in the zoom marketplace and copy/paste credentials as follow:

    - Client ID in the ZOOMCONF_APIKEY of the config file.
    - Client Secret in the ZOOMCONF_APISECRET of the config file.

2. Server-to-Server OAuth

    Is needed to generate and load meeting in zoom.
    Please configure a Server-to-Server OAuth type app in the zoom marketplace and copy/paste credentials as follow:

    - Account ID in the ZOOMCONF_S2S_ACCOUNTID of the config file.
    - Client ID in the ZOOMCONF_S2S_CLIENTID of the config file.
    - Client Secret in the ZOOMCONF_S2S_CLIENTSECRET of the config file.
