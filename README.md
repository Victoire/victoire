# Victoire DCMS

##Overview

Basé sur **Symfony2**, le coeur de Victoire (**VictoireCoreBundle**) pose les bases du DCMS et peut s'installer dans n'importe quel projet Symfony2 en quelques minutes.

L'enjeu principal est de permettre au client final de pouvoir **créer**/**modifier**/**supprimer** chaque contenu présent sur son site, autant pour un contenu dit static que pour du contenu basé sur ses objets métiers et **sans avoir la moindre compétence en développement** et avec le minimum de formation possible.

Par son approche **modulaire**, VictoireDCMS mise sur son éco-système à base de **widgets**, qui devient de plus en plus riche à mesure que le nombre de projets réalisés augmente car au delà de l'enjeu principal, VictoireDCMS doit permettre de réutiliser les mêmes widgets d'un projet à un autre même pour des logiques métiers totalement différentes.



##What's inside?

Victoire est composé de plusieurs composants lui permettant de fonctionner :


Composant | Description
------------ | -------------
[**BlogBundle**][2] | Permet de créer et gérer un ou plusieurs blog
[**BusinessEntityBundle**][3] | Gère ce qui a à trait aux entités métiers façon Victoire
[**BusinessEntityPageBundle**][4] | Défini et gère les pages de type [BusinessEntityPage][18] et [BusinessEntityPagePattern][19
[**CoreBundle**][1] | Responsable de la définition de l'architecture et comprenant la logique de base
[**DashboardBundle**][5] | Responsable d'accueillir l'utilisateur après l'installation d'un nouveau projet
[**FormBundle**][6] | Amène le thème des formulaires Victoire (basé sur MopaBootstrapBundle) theme
[**MediaBundle**][7] | Basé sur KunstmaanMediaBundle, il offre une interface de gestion des fichiers d'un site et est une passerelle avec les widgets
[**PageBundle**][8] | Un des bundles les plus importants, il est responsable des pages de bases
[**QueryBundle**][9] | Contient la logique permettant à tout objet de récupérer des entités métiers
[**SeoBundle**][10] | Plus rien ne vous fait peur en terme de SEO. Ce bundle, apporte ce qu'il faut pour définir la sémantique d'une vue et ses détails de reférencement
[**TemplateBundle**][11] | Que serais-je le monde sans patrons ! Ce bundle apporte la structure pour encadrer les patrons comme il se doivent de l'être
[**TestsBundle**][12] | Contient l'ensemble des contextes et tests Behat
[**TwigBundle**][13] | Apporte ce qu'il faut pour offrir de la flexibilité même pour les pages d'erreur (selon code apache 404, 500, 503 etc ) et d'autres fonctionnalités propres à Twig
[**UserBundle**][14] | Hérite de FosUserBundle, ce bundle offre un système d'utilisateurs prêt à l'emploi
[**WidgetBundle**][15] | Composant omniprésent dans Victoire, il s'occupe de tout ce qui à trait aux base des widgets, de la définition à l'affichage en passant par les formulaires de création et modifications multi-mode
[**WidgetMapBundle**][16] | Contient la logique pour la gestion des widgetsMaps, éléments importants dans l'architecture des vues


* Read the [setup guide](http://github.com/victoire/victoire//blob/master/setup.md)

[![Licence Creative Commons](http://i.creativecommons.org/l/by-nc-nd/4.0/88x31.png)](http://creativecommons.org/licenses/by-nc-nd/4.0/)
[![SensioLabs Insights Badge](https://insight.sensiolabs.com/projects/067bfdfc-d517-4537-8ce4-e8b5008bfff0/small.png)](https://insight.sensiolabs.com/projects/067bfdfc-d517-4537-8ce4-e8b5008bfff0)
[![Documentation Status](https://readthedocs.org/projects/victoiredcms/badge/?version=latest)](https://readthedocs.org/projects/victoiredcms/?badge=latest)

Cette œuvre est mise à disposition selon les termes de la Licence Creative Commons Attribution - Pas d'Utilisation Commerciale - Pas de Modification 4.0 International.

*Proprieté d'AppVentus, tous droits réservés - Property of AppVentus, All Right reserved*


[1]:  http://github.com/victoire/victoire//blob/master/Bundle/CoreBundle/README.md
[2]:  http://github.com/victoire/victoire//blob/master/Bundle/BlogBundle/README.md
[3]:  http://github.com/victoire/victoire//blob/master/Bundle/BusinessEntityBundle/README.md
[4]:  http://github.com/victoire/victoire//blob/master/Bundle/BusinessEntityPageBundle/README.md
[5]:  http://github.com/victoire/victoire//blob/master/Bundle/DashboardBundle/README.md
[6]:  http://github.com/victoire/victoire//blob/master/Bundle/FormBundle/README.md
[7]:  http://github.com/victoire/victoire//blob/master/Bundle/MediaBundle/README.md
[8]:  http://github.com/victoire/victoire//blob/master/Bundle/PageBundle/README.md
[9]:  http://github.com/victoire/victoire//blob/master/Bundle/QueryBundle/README.md
[10]: http://github.com/victoire/victoire//blob/master/Bundle/SeoBundle/README.md
[11]: http://github.com/victoire/victoire//blob/master/Bundle/TemplateBundle/README.md
[12]: http://github.com/victoire/victoire//blob/master/Bundle/TestsBundle/README.md
[13]: http://github.com/victoire/victoire//blob/master/Bundle/TwigBundle/README.md
[14]: http://github.com/victoire/victoire//blob/master/Bundle/UserBundle/README.md
[15]: http://github.com/victoire/victoire//blob/master/Bundle/WidgetBundle/README.md
[16]: http://github.com/victoire/victoire//blob/master/Bundle/WidgetMapBundle/README.md
[18]: http://github.com/victoire/victoire//blob/master/Bundle/BusinessEntityPageBundle/Resources/doc/BusinessEntityPage.md
[19]: http://github.com/victoire/victoire//blob/master/Bundle/BusinessEntityPageBundle/Resources/doc/BusinessEntityPagePattern.md
