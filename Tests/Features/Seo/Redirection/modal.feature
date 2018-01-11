@mink:selenium2 @reset-schema @alice(Page) @alice(Redirection)
Feature: Create new redirection

  Background:
    Given I maximize the window
    When I am on "/fr"
    And I open the redirection menu
    And I wait 1 second

  Scenario: I can view the new error
    Then the modal title should be "REDIRECTIONS"
    And the list "redirections-list-container" should contain 3 element
    And I should see text matching "http://victoire/fr/input-1"
    And I should see text matching "http://victoire/fr/input-2"
    And I should see text matching "http://victoire/fr/input-3"
