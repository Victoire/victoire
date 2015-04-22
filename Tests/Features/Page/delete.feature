@mink:selenium2 @database @fixtures
Feature: Delete a page

Background:
    Given I am logged in as "anakin@victoire.io"

Scenario: I can delete a new page
    Given I am on "/fr/test"
    Given I should see "Page"
    Given I select the option "Paramètres de la page" in the dropdown "Page"
    Then I should see "Supprimer"
    Given I follow "Supprimer"
    Then I should see "Cette action va supprimer définitivement cette page. Cette action est irréversible. Etes vous sûr ?"
    Given I press "J'ai bien compris, je confirme la suppression"
    Then I should see "Victoire"
    And I wait 10 seconds
    And I should be on "/fr/"
