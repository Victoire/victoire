@mink:selenium2 @alice(Page) @reset-schema
Feature: Test asynchronous widget

  Background:
    Given I maximize the window
    And I am on homepage

  Scenario: I create an asynchronous widget
    When I switch to "layout" mode
    And I should see "Nouveau contenu"
    And I select "Force" from the "1" select of "content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Obscure"
    And I check the "Chargement asynchrone ?" checkbox
    And I submit the widget
    Then I should see "Victoire !"
    Given I reload the page
    Then I should see "Le côté Obscure de la force"
