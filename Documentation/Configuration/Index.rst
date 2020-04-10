.. include:: ../Includes.txt


.. _configuration:

=============
Configuration
=============

All administration is done in the configuration section of this extension.

Allowed HTML Tags
=================

Comma separated list with HTML elements. Each element in the list will be randomly
used to obfuscate the email address. Its recommended to use only inline elements.
You can add or remove other Elements. If the list is empty only the <span> tag will
be used.

Allowed CSS Selectors
=====================

Comma separated list with CSS Selectors. Each selector will be added to the CSS File
with option “display: none;” like this:

.. code-block:: css

    .mySelectorName {
        display: none;
    }

You should edit this option if you want to use different selector names OR if one or
more of the selectors in the list are already used within your website CSS
configuration to prevent overwriting existing CSS selectors.

Prefix for allowedCSSSelectors
==============================

If you don't know exactly which CSS selectors are already used then add a prefix.
Adding prefix “mySuperSecretPrefix” will result in this CSS definition:

.. code-block:: css

    .mySuperSecretPrefixmySelectorName {
        display: none;
    }


