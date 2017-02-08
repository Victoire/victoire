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
        Given the following BusinessTemplate:
            | currentLocale |name                       | backendName  | slug                     |  businessEntityId | parent  | template |
            | fr            |Fiche Jedi - {{item.name}} | Fiche Jedi   | fiche-jedi-{{item.slug}} |  jedi             | home    | base     |
        Given I am on "/fr/fiche-jedi-anakin"
        And I switch to "layout" mode
        And I should see "Nouveau contenu"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Créer"
        When I fill in "Côté de la force" with "Nouveau"
        And I submit the widget
        And I wait 5 seconds
        Then I should see "Le côté Nouveau de la force"

        Given I am on "/victoire-dcms/backend/jedi/1/edit"
        And I press "Supprimer"
        And I should be on "victoire-dcms/backend/jedi/"

        Given I am on "/fr/fiche-jedi-anakin"
        Then I should see "404 Not Found"
