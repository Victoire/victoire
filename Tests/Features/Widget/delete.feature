@mink:selenium2 @alice(Page) @reset-schema
Feature: Delete a widget

Background:
    Given I am logged in as "anakin@victoire.io"

Scenario: Abort Delete
    Then I switch to "layout" mode
    When I select "Force" from the "1" select of "content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Obscure"
    And I submit the widget
    Then I should see "Victoire !"
    Given I switch to "edit" mode
    And I edit the "Force" widget
    Then I should see "Supprimer"
    Given I follow "Supprimer"
    Then I should see "Cette action va définitivement supprimer ce contenu. Cette action est irréversible. Êtes-vous sûr ?"
    Given I press "Annuler"
    And I wait 1 second
    And I follow "Annuler"
    Then I should see "Le Côté Obscure de la force"
    When I reload the page
    Then I should see "Le Côté Obscure de la force"

Scenario: Create and delete a widget
    Then I switch to "layout" mode
    When I select "Force" from the "1" select of "content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Obscure"
    And I submit the widget
    And I wait 10 seconds
    Given I switch to "edit" mode
    And I edit the "Force" widget
    Then I should see "Supprimer"
    Given I follow "Supprimer"
    Then I should see "Cette action va définitivement supprimer ce contenu. Cette action est irréversible. Êtes-vous sûr ?"
    Given I press "J'ai bien compris, je confirme la suppression"
    Then I wait 2 seconds
    And I should not see "Widget #"
    And I should not see "Le Côté Obscure de la force"
