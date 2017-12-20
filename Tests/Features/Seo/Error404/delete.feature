@mink:selenium2 @alice(Page) @reset-schema
Feature: Delete a 404 error

  Background:
    Given I maximize the window
    When I am on homepage
    Then I open the hamburger menu
    And I wait 1 second
    And I follow "menu-404-sub-item" from the "menu-redirection-main-item" dropdown menu
    And I wait 1 second

  @alice(Error404)
  Scenario: I can delete an error
    When I follow the "delete-link-1" id
    And I wait 1 second
    Then I should see "Cette action va supprimer définitivement cette page. Cette action est irréversible. Êtes-vous sûr ?"
    When I press "J'ai bien compris, je confirme la suppression"
    And I wait 1 second
    Then I should see 2 rows in the table
    And I should not see "http://victoire/fr/test1"
    And I should see "http://victoire/fr/test2"
    And I should see "http://victoire/fr/test3"