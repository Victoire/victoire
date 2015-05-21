# Victoire I18nBundle

This bundle deals with the multilingual versions of a website.
It enables the translation of the integrality of Victoire's pages in few steps.

#Configuration


    victoire_i18n:
        victoire_locale: en
        available_locales:
            fr: fr
            en: en
            it: it
            de: de
            es: es
        locale_pattern: domain
        locale_pattern_table:
            example.fr: fr
            example.en: en
            example.it: it
            example.de: de
            example.es: es

The line

    victoire_locale: en

sets Victoire's interface language.
*So far, Victoire is not totally translated in english. If the translation doesnt exist in this bundle's admin interface, you can translate the admin with Symfony's fallback*

The line

    locale_pattern: domain

indicates to the bundle the pattern for locale resolution.
*So far, the locale pattern domain is the only one implemented. Depending on the future needs, other patterns could be created*

You have to add the locale pattern table to the locale domain which sets the correspondence between the domains and the targeted locale.

#Commands

I18nBundle has a migration commande towards i18n :

    php bin/console victoire:migrate:i18n

This command allows, among other actions, to migrate your achitecture towards a translatable one.
By default, the website is considered as French, so as all its pages.

There is an option to change the locale default settings to implemente the default language as desired.
i.e : if your website is set to be in english, you shall execute :

    php bin/console victoire:migrate:i18n --default-locale=en
