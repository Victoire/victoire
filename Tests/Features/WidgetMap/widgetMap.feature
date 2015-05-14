@mink:selenium2 @alice(Page) @reset-schema
Feature: Test widgetMap

Background:
    Given I am logged in as "anakin@victoire.io"

Scenario: I create widget in a position
    Then I switch to "layout" mode
    When I select "Force" from the "1" select of "content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Obscure"
    And I submit the widget
    Then I should see "Victoire !"
    And I should see "Le côté Obscure de la force"

    When I select "Force" from the "2" select of "content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Lumineux"
    And I submit the widget
    Then I should see "Victoire !"
    Then I should see "Le côté Lumineux de la force"
    And "Le côté Obscure de la force" should precede "Le côté Lumineux de la force"

    Given I reload the page
    Then "Le côté Obscure de la force" should precede "Le côté Lumineux de la force"

    Given I select "Force" from the "2" select of "content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Double"
    And I submit the widget
    Then I should see "Victoire !"
    Then I should see "Le côté Double de la force"
    And "Le côté Lumineux de la force" should precede "Le côté Double de la force"
    And "Le côté Double de la force" should precede "Le côté Obscure de la force"

    Given I reload the page
    And "Le côté Lumineux de la force" should precede "Le côté Double de la force"
    And "Le côté Double de la force" should precede "Le côté Obscure de la force"