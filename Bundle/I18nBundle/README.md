# Victoire I18nBundle

intégré a victoire c'est un composant pour gérer les sites multilingues. Il permet de traduire toutes les pages dcms de victoire en quelques clic.

#Configuration type


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
  
  la ligne
  
    victoire_locale: en
    
 permet de donner à victoire la langue de son interface. Pour le moment victoire n'est pas intégralement traduit en anglais mais cela va venir. Si la traduction n'existe pas dans l'interface d'administration ce bundle traduire l'administration avec la fallback de symfony

la ligne 

    locale_pattern: domain
    
donne au bundle le pattern pour résoudre la locale, pour le moment seul le local pattern domain est implémenté mais en fonction des besoins futurs d'autres patterns pourront voir le jour.

au pattern domain il faut rajouter le local pattern table qui permet de faire correspondre les domaines à la locale cible.

#commandes

I18nBundle dispose d'une commande de migration vers i18n.

pour l'appeler dans la console tappez

    php bin/console victoire:migrate:i18n

cette commande permet entre autre de migrer votre architecture vers une architecture traduisible. par default le site est considéré comme français donc toutes les pages seront considérées comme françaises.

il existe une option pour changer la locale par défaut si votre site est un si anglais pas exemple il faudra tapper:

    php bin/console victoire:migrate:i18n --default-locale=en