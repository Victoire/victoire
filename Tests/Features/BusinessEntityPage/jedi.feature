@mink:selenium2 @alice(Page) @alice(User) @reset-schema
Feature: Manage jedis

    Background:
        Given I maximize the window
        And I am on homepage

    Scenario: I can list jedis
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 27700         | anakin |
            | Yoda   | bright | 17700         | yoda   |
        And I open the hamburger menu
        Then I should see "Jedi"
        When I follow "Jedi"
        Then I should be on "/victoire-dcms/backend/jedi/"
        Then I should see "Liste des Jedis"
        Then I should see the following table
            | Nom    | Medichloriens | Côté de la force |
            | Anakin | 27700         | dark             |
            | Yoda   | 17700         | bright           |

    Scenario: I try to list jedis but there is no results
        Given I am on "/victoire-dcms/backend/jedi/"
        Then I should see "Aucun résultat"

    Scenario: I create a new jedi
        Given I am on "/victoire-dcms/backend/jedi/"
        Then I should see "Aucun résultat"
        When I follow "Nouveau jedi"
        Then I should be on "/victoire-dcms/backend/jedi/new"
        And I should see "Nouveau Jedi"
        When I fill in "Nom" with "Anakin"
        And I fill in "MediChloriens" with "27700"
        And I fill in "Identifiant" with "anakin"
        And I select "Obscure" from "Coté de la force"
        And I press "Créer"
        Then I should be on "/victoire-dcms/backend/jedi/"
        Then I should see "Liste des Jedis"
        Then I should see the following table
          | Nom    | Medichloriens | Côté de la force |
          | Anakin | 27700         | dark             |

    Scenario: I delete a jedi
        Given the following Jedis:
          | name   | side   | midiChlorians | slug   |
          | Anakin | dark   | 27700         | anakin |
          | Yoda   | bright | 17700         | yoda   |
        And I am on "/victoire-dcms/backend/jedi/"
        Then I should see the following table
          | Nom    | Medichloriens | Côté de la force |
          | Anakin | 27700         | dark             |
          | Yoda   | 17700         | bright           |
        And I follow the 1st "Modifier" link
        Then I should see "Modification de Jedi"
        When I press "Supprimer"
        Then I should be on "/victoire-dcms/backend/jedi/"
        Then I should see "Liste des Jedis"
        Then I should see the following table
            | Nom    | Medichloriens | Côté de la force |
            | Yoda   | 17700         | bright           |

    Scenario: I can rename the url of a jedi
        Given the following Jedis:
          | name   | side   | midiChlorians | slug   |
          | Anakin | dark   | 27700         | anakin |
        When I open the hamburger menu
        Then I should see "Représentation métier"
        When I follow "Représentation métier"
        Then I should see "Ajouter une représentation métier"
        When I follow the tab "Jedi"
        And I follow "Ajouter une représentation métier"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi - {{item.name}}"
        And I fill in "URL" with "fiche-jedi-{{item.slug}}"
        And I follow "Créer"
        And I wait 5 seconds
        Then I should see "La représentation métier a bien été créée"
        Given I am on "/fr/fiche-jedi-anakin"
        And I switch to "layout" mode
        And I should see "Nouveau contenu"
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I fill in "Côté de la force" with "Nouveau"
        And I submit the widget
        And I wait 5 seconds
        Then I should see "Le Côté Nouveau de la force"
        Given I select the option "Paramètres de la page" in the dropdown "Page"
        And I should see "Mettre à jour"
        When I fill in "business_page_translations_fr_slug" with "Dark Vador"
        Then I should see an ".business_page_translations_fr_a2lix_translationsFields-fr .slug-is-correct.vic-hidden" element
        And I should not see an ".business_page_translations_fr_a2lix_translationsFields-fr .slug-is-not-correct.vic-hidden" element
        When I fill in "business_page_translations_fr_slug" with ""
        Then I should not see an ".business_page_translations_fr_a2lix_translationsFields-fr .slug-is-not-correct.vic-hidden" element
        And I should see an ".business_page_translations_fr_a2lix_translationsFields-fr .slug-is-correct.vic-hidden" element
        When I fill in "business_page_translations_fr_slug" with "dark-vador"
        Then I should not see an ".business_page_translations_fr_a2lix_translationsFields-fr .slug-is-correct.vic-hidden" element
        And I should see an ".business_page_translations_fr_a2lix_translationsFields-fr .slug-is-not-correct.vic-hidden" element
        When I submit the widget
        And I wait 5 seconds
        Then I should see "Page modifiée avec succès"
        And I should be on "/fr/dark-vador"
