# ADA module for Etherpad Integration

## Module Install

1. import ``db/ada_etherpad-integration_module.sql`` in the provider DB.
2. import ``db/module_menu.sql`` in the common DB or each provider DB if it's a non multiprovider installation with distinct menu trees.
3. run ``composer install`` at the module's root dir
4. copy ``config/config_DEFAULT.inc.php`` to ``config/config.inc.php``
5. edit ``config/config.inc.php`` changing the following defines:

| define  |   |
|---|---|
| **MODULES_ETHERPAD_HOST** | Etherpad host url
| **MODULES_ETHERPAD_PORT** | Etherpad port (usually empty if above url is https)
| **MODULES_ETHERPAD_APIBASEURL** | REST API endpoint base url (defaults to 'api', should be no need change it)
| **MODULES_ETHERPAD_APIKEY** | The Etherpad api key, as found in your Etherpad installation APIKEY.txt file
| **MODULES_ETHERPAD_INSTANCEPAD** | True to enable one pad per course instance
| **MODULES_ETHERPAD_NODEPAD** | True to enable one pad per course node

**NOTE**: each define can be either in the module or client config (for non multiprovider installs). Client define takes precedence over the module config.

## Module behaviour
- Tutors and Students will both get a menu item under the 'Do' menu tree, one for each enabled pad type (instance and/or node)
- Tutors have privileges to create new pads, so they will always have the menu items enabled
- Students will see the menu item only if the tutor has created a pad beforehand

## New pad default text

Two files are used to customize a new pad default text: ``instanceemptypad.txt`` and ``nodeemptypad.txt``.
They can be either in the module's config dir or in the client config dir. Clients files take precedence over module.

In the node file only, you can use some placeholders that will be replaced with node data. (E.g.: ``%name`` will be replaced with the node name). Pls refer to the array retuned by the ``AMA_Tester_DataHandler::get_node_info`` method: the placeholders are the array keys enclosed with a percent sign (``%``).
