@mink:selenium2 @alice(Page) @reset-schema
Feature: Manage sitemap

  Background:
    Given I maximize the window
    And I am on homepage

  @smartStep
  Scenario: I can create a child for a page
    Given I should see "Page"
    Given I select the option "Nouvelle page" in the dropdown "Page"
    And I should see "Créer"
    And I fill in "Nom" with "anakin skywalker"
    Then I submit the widget
    And I should see "Page créée avec succès"
    And I should be on "/fr/anakin-skywalker"
    Given I select the option "Nouvelle page" in the dropdown "Page"
    And I should see "Créer"
    And I fill in "Nom" with "luke skywalker"
    And I select "anakin skywalker" from "Page parente"
    Then I submit the widget
    And I should see "Page créée avec succès"
    And I wait 2 seconds
    And I should be on "/fr/anakin-skywalker/luke-skywalker"

  Scenario: I can delete a page and his child
    Given the following Page:
      | currentLocale |name     | slug     | parent  | template      |
      | fr            |anakin skywalker | anakin-skywalker | home    | base          |
      | fr            |luke skywalker | luke-skywalker | anakin-skywalker    | base          |
    And I am on "/fr/anakin-skywalker"
    Given I select the option "Paramètres de la page" in the dropdown "Page"
    Then I should see "Supprimer"
    Then I follow "Supprimer"
    And I should see "Cette action va supprimer définitivement cette page. Cette action est irréversible. Êtes-vous sûr ?"
    And I press "J'ai bien compris, je confirme la suppression"
    And I wait 2 seconds
    Given I am on "/fr/anakin-skywalker/luke-skywalker"
    Then I should see "404 not found"
