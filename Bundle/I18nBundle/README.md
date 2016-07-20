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

Run the ImportViewTranslationsCommand to import (or update) the views translations.

    php bin/console victoire:i18n:import_view_translations --src traductions.tsv

Prepare your file like this:

#myTranslationFile.tsv
```
id	FR	EN	DE	ES	IT	NL
9	A propos	About	Über Uns	Sobre nosotros	A proposito	over
...
```
`tsv stands for Tab separated values and you can easily generate one with google spreadsheet`

You can specify the delimiter (a tab `\t` by default) by passing the `--delimiter ***` option

> [Free translation prototype document](https://docs.google.com/spreadsheets/d/1qoirIucMy_3aK3zz962vb2k8BrcBNC8GySKV-aOSsj4/edit?usp=sharing)