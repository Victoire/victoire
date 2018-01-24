@mink:selenium2 @reset-schema @alice(Page) @alice(Redirection)
Feature: Delete a redirection

  Background:
    Given I maximize the window
    And I am on homepage
    And I open the redirection menu
    And I wait 1 second

  Scenario: I can delete an error
    Given the list "redirections-list-container" should contain 3 elements
    When I click the "#delete-link-1" element
    And I wait 1 second
    Then I click the "#delete-button" element
    And I wait 1 second
    Then the list "redirections-list-container" should contain 2 elements
    And I should see text matching "Redirection successfully deleted!"
