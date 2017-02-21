@mink:selenium2 @alice(Page) @reset-schema
Feature: Delete a page

Background:
    Given I maximize the window
    And I am on homepage

Scenario: I can delete a new page
    Given I am on "/fr/test"
    And I open the settings menu
    Then I should see "Supprimer"
    When I follow the link containing "Supprimer"
    Then I should see "Cette action va supprimer définitivement cette page. Cette action est irréversible. Êtes-vous sûr ?"
    Given I press "J'ai bien compris, je confirme la suppression"
    And I should be on "/fr/"
