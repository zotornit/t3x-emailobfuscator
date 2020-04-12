.. include:: Includes.txt

.. _start:

=============================================================
emailobfuscator - TYPO3 extension
=============================================================

:Version:
   |release|

:Language:
   en

:Authors:
   Thomas Pronold | zotorn.de

:Email:
   tp@zotorn.de

:License:
   GPL 2 or later


What does it do?
===================

Replaces the default email address spam protection with a better one.
The email obfuscation is more randomized, safer and more user friendly for the website visitor.


Why is ‘config.spamProtectEmailAddresses’ a bad idea?
======================================================

.. rst-class:: bignums

#. Only TYPOLINKS are obfuscated. Plain emails or manual HTML email link markup is ignored.

#. The user cannot right click and copy-paste the email address.


What changed with version 6?
=============================

Version 6 is a complete new approach to solve the email obfuscation issue.
I have removed a lot of old code and wrote the extension from scratch.

The most important change is, that you have to enable the extension with TypoScript now.

.. code-block:: typoscript

    plugin.tx_emailobfuscator.settings {
        enabled = 1
    }

Since everything is new, the state is set back to **BETA**

.. toctree::
  :hidden:
  :caption: BASICS

  Installation/Index
  Configuration/Index
