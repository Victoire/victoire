@mink:selenium2 @alice(Page) @reset-schema
Feature: Create business entity pages

    Background:
        Given I am logged in as "anakin@victoire.io"
        And the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        And I resize the window to 1024x720

    Scenario: I can delete an BE and his BEP
        Given I open the hamburger menu
        Then I should see "Représentation métier"
        When I follow "Représentation métier"
        Then I should see "Ajouter une représentation"
        When I follow the tab "Jedi"
        And I follow "Ajouter une représentation"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi - {{item.name}}"
        And I fill in "Url" with "fiche-jedi-{{item.slug}}"
        And I follow "Créer"
        Then I should see "La représentation métier a bien été créé"
        And I wait 5 seconds
        And I should be on "/fr/fiche-jedi-%7B%7Bitem.slug%7D%7D"

        Given I am on "/fr/fiche-jedi-anakin"
        And I switch to "layout" mode
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I fill in "Côté de la force" with "Nouveau"
        And I submit the widget
        Then I should see "Le Côté Nouveau de la force"

        Given I am on "/victoire-dcms/backend/jedi/1/edit"
        And I press "Supprimer"
        And I should be on "victoire-dcms/backend/jedi/"

        Given I am on "/fr/fiche-jedi-anakin"
        Then I should see "404 not found"
