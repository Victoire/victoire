@mink:selenium2 @alice(Page) @reset-schema
Feature: Display the redirection modal

  Background:
    Given I maximize the window
    When I am on homepage
    Then I open the hamburger menu
    And I follow "menu-redirection-sub-item" from the "menu-redirection-main-item" dropdown menu
    And I wait 1 second

  @alice(Redirection)
  Scenario: I can view the redirection modal's content
    Given I should see 3 rows in the table
    And The modal title should be "Liste des redirections"
    Then I should see "http://victoire/fr/input-1"
    And I should see "http://victoire/fr/output-1"
    And I should see "http://victoire/fr/input-2"
    And I should see "http://victoire/fr/output-2"
    And I should see "http://victoire/fr/input-3"
    And I should see "http://victoire/fr/output-3"