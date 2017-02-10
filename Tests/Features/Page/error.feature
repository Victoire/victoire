@mink:selenium2 @alice(Page) @alice(ErrorPage) @reset-schema
Feature: Page not found

Background:
    Given I maximize the window
    And I am on homepage

  Scenario: I cannot acces a non exitant page
    And I am on "/fr/imaginary-page"
    Then I should see "404 not found"
  Scenario: I cannot acces a page for a non exitant locale
    And I am on "/notalocale/"
    Then I should see "404 not found"
  Scenario: I cannot acces a non existant page for a non exitant locale
    And I am on "/notalocale/imaginary-page"
    Then I should see "404 not found"
  Scenario: I cannot acces a page for a inconsistant locale
    And I am on "/notalocale:/"
    Then I should see "404 not found"
