@mink:selenium2 @alice(Page) @reset-schema
Feature: Create a page

  Background:
    Given I maximize the window
    And I am on homepage

  @smartStep
  Scenario: I check the domain strategy
    Given I visit homepage througth domain "fr.victoire.io"
    Then the title should be "Page d'accueil"
    Given I visit homepage througth domain "en.victoire.io"
    Then the title should be "Homepage"

