@mink:selenium2 @alice(Page) @reset-schema
Feature: Delete a widget

    Background:
        Given I maximize the window

Scenario: Abort Delete
    Given the following WidgetMap:
        | view | action | slot |
        | home | create | main_content |
    Given the following WidgetForce:
        | widgetMap                | side |
        | home | Obscur |
    And I am on homepage
    Given I switch to "edit" mode
    And I edit the "Force" widget
    Then I should see "SUPPRIMER"
    Given I follow the link containing "SUPPRIMER"
    Then I should see "Cette action va définitivement supprimer ce contenu. Cette action est irréversible."
    And I should see "Êtes-vous sûr ?"
    Given I press "Annuler"
    And I wait 1 second
    And I follow the link containing "Annuler"
    Then I should see "Le côté Obscur de la force"
    When I reload the page
    Then I should see "Le côté Obscur de la force"

Scenario: Create and delete a widget
    And I am on homepage
    Then I switch to "layout" mode
    And I should see "Nouveau contenu"
    When I select "Force" from the "1" select of "main_content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Obscur"
    And I submit the widget
    Then I should see "Le côté Obscur de la force"


Scenario: Delete a widget
    Given the following WidgetMap:
        | view | action | slot |
        | home | create | main_content |
    Given the following WidgetForce:
        | widgetMap                | side |
        | home | Obscur |
    And I am on homepage
    Given I switch to "edit" mode
    And I edit the "Force" widget
    Then I should see "SUPPRIMER"
    Given I follow the link containing "SUPPRIMER"
    Then I should see "Cette action va définitivement supprimer ce contenu. Cette action est irréversible."
    And I should see "Êtes-vous sûr ?"
    Given I press "J'ai bien compris, je confirme la suppression"
    Then I wait 2 seconds
    And I should not see "Le côté Obscur de la force"
