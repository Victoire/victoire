@mink:selenium2 @alice(Page) @reset-schema
Feature: Domain strategy

  Background:
    Given I maximize the window
    And I am on homepage

  @smartStep
  Scenario: I check the domain strategy
    Given I visit homepage through domain "en.victoire.io"
    Then the title should be "Homepage"
    Given I visit homepage through domain "fr.victoire.io"
    Then the title should be "Page d'accueil"

