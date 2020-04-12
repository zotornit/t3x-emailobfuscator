.. include:: ../Includes.txt


.. _configuration:

=============
Configuration
=============

All configuration is done using TypoScript. This allows for individual adjustments on a per page basis.

Properties
==========



enabled
------

.. container:: table-row

    Property
         enabled

    Data type
         boolean (default: false)

    Description
         Enable/Disable the obfuscation for the given page.



.. code-block:: typoscript

    plugin.tx_emailobfuscator.settings.enabled = 1





obfuscateEmailLinks
------

.. container:: table-row

    Property
         obfuscateEmailLinks

    Data type
         boolean (default: true)

    Description
         Enable/Disable the obfuscation for email links like :code:`<a href="mailto:mail@domain.tld">Send email</a>`.
         If your website does not support JavaScript, disable this option.



.. code-block:: typoscript

    plugin.tx_emailobfuscator.settings {
        enabled = 1
        obfuscateEmailLinks = 0
    }


obfuscatePlainEmails
------

.. container:: table-row

    Property
         obfuscatePlainEmails

    Data type
         boolean (default: true)

    Description
         Enable/Disable the obfuscation for plain emails like :code:`mail@domain.tld`



.. code-block:: typoscript

    plugin.tx_emailobfuscator.settings {
        enabled = 1
        obfuscatePlainEmails = 1
    }


patternEmailLinks
------

.. container:: table-row

    Property
         patternEmailLinks

    Data type
         boolean (default: :code:`/<a[^>]*?href=['"]mailto:[.\s\S]*?<\s*\/\s*a\s*>/i`)

    Description
         The regex pattern which will search for email links. Usually you do not want to change this. It is primary for testing purposes.



.. code-block:: typoscript

    plugin.tx_emailobfuscator.settings {
        enabled = 1
        patternEmailLinks = /<a[^>]*?href=['"]mailto:[.\s\S]*?<\s*\/\s*a\s*>/i
    }


patternPlainEmails
------

.. container:: table-row

    Property
         patternPlainEmails

    Data type
         boolean (default: :code:`/[a-zA-Z.0-9-+]+@[a-zA-Z.0-9-]+/i`)

    Description
         The regex pattern which will search for plain emails. Usually you do not want to change this. It is primary for testing purposes.



.. code-block:: typoscript

    plugin.tx_emailobfuscator.settings {
        enabled = 1
        patternPlainEmails = /[a-zA-Z.0-9-+]+@[a-zA-Z.0-9-]+/i
    }
