ADA GDPR Module
-----------------------

This module implements GDPR related requests management

## Installation Notes

1. If it's not installed, install php *composer* either locally or globally as described at [https://getcomposer.org/download/](https://getcomposer.org/download/)

1. Run ``composer install`` at module's root. This will install the needed dependencies.

1. Run the provided ``db/ada_gdpr_privacy.sql`` script:

    * on the **common DB** if in a **multiprovider environment**
    _NOTE_: In a *multiprovider* environment, you can only have a single set of privacy content.    
    In a *NON multiprovider* environment, you can either have a shared set of privacy policies (in the common db) or a per-provider set of policies.
        
    * on each provider's db if in a **NON multiprovider** environment and you need a per-provider set of privacy policies.

1. Run the provided ``db/ada_gdpr_module.sql`` script on each provider's db to create and populate this module's own tables.

1. Run the provided ``db/module_menu.sql`` script on the common db only, to generate module's own menu tree and items.
