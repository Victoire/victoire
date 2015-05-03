@mink:selenium2 @alice(Page) @reset-schema
Feature: Delete a widget

Background:
    Given I am logged in as "anakin@victoire.io"

Scenario: Create and delete a widget
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
    Then I should see "Cette action va supprimer définitivement ce contenu. Cette action est irréversible. Etes vous sûr ?"
    Given I press "J'ai bien compris, je confirme la suppression"
    Then I should see "Victoire"

