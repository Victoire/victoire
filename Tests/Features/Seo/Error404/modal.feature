@mink:selenium2 @alice(Page) @reset-schema
Feature: Display the 404 error modal

  Background:
    Given I maximize the window
    When I am on homepage
    Then I open the hamburger menu
    And I follow "menu-404-sub-item" from the "menu-redirection-main-item" dropdown menu
    And I wait 1 second

  @alice(Error404)
  Scenario: I can view the 404 error modal's content
    Then I should see 3 rows in the table
    And The modal title should be "Liste des erreurs 404"
    And I should see "http://victoire/fr/test1"
    And I should see "http://victoire/fr/test2"
    And I should see "http://victoire/fr/test3"