@mink:selenium2 @reset-schema @alice(Page)
Feature: Create new error

  Background:
    Given I maximize the window
    And I am on "/fr/fake"
    When I am on "/fr"
    And I open the 404 menu
    And I wait 1 second

  Scenario: I can view the new error
    Then The modal title should be "404 ERRORS"
    And the list "route-list" should contain 1 element
    And I should see text matching "https?:\/\/.+\/app_ci\.php\/fr\/fake"
