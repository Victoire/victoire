@mink:selenium2 @alice(Page) @reset-schema
Feature: Delete a redirection

  Background:
    Given I maximize the window
    When I am on homepage
    Then I open the hamburger menu
    And I wait 1 second
    And I follow "menu-redirection-sub-item" from the "menu-redirection-main-item" dropdown menu
    And I wait 1 second

  @alice(Redirection)
  Scenario: I can delete an error
    Then I should see "http://victoire/fr/input-1"
    When I follow the "delete-link-1" id
    And I wait 1 second
    Then I should see "Cette action va supprimer définitivement cette page. Cette action est irréversible. Êtes-vous sûr ?"
    Then I press "J'ai bien compris, je confirme la suppression"
    And I wait 1 second
    Then I should see 2 rows in the table
    And I should not see "http://victoire/fr/input-1"
