@mink:selenium2 @alice(Page) @reset-schema
Feature: Manage sitemap

  Background:
    Given I maximize the window
    And I am on homepage

  @smartStep
  Scenario: I can create a child for a page
    When I follow the float action button
    And I should see "Nouvelle page"
    When I follow the link containing "Nouvelle page"
    Then I should see "Créer"
    When I fill in "Nom" with "anakin skywalker"
    Then I submit the widget
    And I should see "Page créée avec succès"
    And I should be on "/fr/anakin-skywalker"
    When I follow the float action button
    Then I should see "Nouvelle page"
    When I follow "Nouvelle page"
    Then I should see "Créer"
    When I fill in "Nom" with "luke skywalker"
    And I select "anakin skywalker" from "Page parente"
    When I submit the widget
    Then I should see "Page créée avec succès"
    When I wait 2 seconds
    Then I should be on "/fr/anakin-skywalker/luke-skywalker"

  Scenario: I can delete a page and his child
    Given the following Page:
      | currentLocale |name     | slug     | parent  | template      |
      | fr            |anakin skywalker | anakin-skywalker | home    | base          |
      | fr            |luke skywalker | luke-skywalker | anakin-skywalker    | base          |
    And I am on "/fr/anakin-skywalker"
    Given I open the settings menu
    Then I should see "Supprimer"
    Then I follow the link containing "Supprimer"
    And I should see "Cette action va supprimer définitivement cette page. Cette action est irréversible. Êtes-vous sûr ?"
    And I press "J'ai bien compris, je confirme la suppression"
    And I wait 2 seconds
    Given I am on "/fr/anakin-skywalker/luke-skywalker"
    Then I should see "404 Not Found"
