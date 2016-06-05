@mink:selenium2 @alice(Page) @reset-schema
Feature: Create business entity pages

    Background:
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        And I maximize the window
        And I am on homepage

    Scenario: I can delete an BE and his BEP
        Given I open the hamburger menu
        Then I should see "Représentation métier"
        When I follow "Représentation métier"
        Then I should see "Ajouter une représentation métier"
        When I follow the tab "Jedi"
        And I should see "Ajouter une représentation métier"
        And I follow "Ajouter une représentation métier"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi - {{item.name}}"
        And I fill in "URL" with "fiche-jedi-{{item.slug}}"
        And I follow "Créer"
        And I wait 5 seconds
        Then I should be on "/fr/victoire-dcms/business-template/show/5"
        And I should see "La représentation métier a bien été créée"
        Given I am on "/fr/fiche-jedi-anakin"
        And I switch to "layout" mode
        And I should see "Nouveau contenu"
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I fill in "Côté de la force" with "Nouveau"
        And I submit the widget
        And I wait 5 seconds
        Then I should see "Le Côté Nouveau de la force"

        Given I am on "/victoire-dcms/backend/jedi/1/edit"
        And I press "Supprimer"
        And I should be on "victoire-dcms/backend/jedi/"

        Given I am on "/fr/fiche-jedi-anakin"
        Then I should see "404 not found"
