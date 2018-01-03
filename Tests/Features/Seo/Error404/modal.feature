@mink:selenium2 @reset-schema @alice(Page) @alice(Error404)
Feature: Display the error's modal

  Background:
    Given I maximize the window
    When I am on "/fr"
    And I open the 404 menu
    And I wait 1 second

  Scenario: I can view the 404 error modal's content
    Then The modal title should be "404 ERRORS"
    And I should see text matching "Routes"
    And The list "route-list" should contain 3 element
    And I should see text matching "Files"
