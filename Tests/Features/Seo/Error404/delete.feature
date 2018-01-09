@mink:selenium2 @reset-schema @alice(Page) @alice(Error404)
Feature: Delete an error

  Background:
    Given I maximize the window
    When I am on "/fr"
    And I open the 404 menu
    And I wait 1 second

  Scenario: I can delete a redirection
    Then the list "route-list" should contain 3 elements
    When I click the "#delete-link-1" element
    And I wait 1 second
    Then I click the "#delete-button" element
    And I wait 1 second
    Then the list "route-list" should contain 2 elements
