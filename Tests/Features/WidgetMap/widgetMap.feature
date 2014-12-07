@mink:selenium2 @database @fixtures
Feature: Create a widget at first position

Background:
    Given I am logged in as "paul@appventus.com"

Scenario: Create a widget at first position
    Then I switch to edit mode "true"
    When I select "Anakin" from the "1" select of "content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Obscure"
    And I submit the widget
    Then I should see "Victoire !"
    And I reload the page
    Then I should see "Le côté Obscure de la force"

    When I select "Anakin" from the "2" select of "content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Lumineux"
    And I submit the widget
    Then I should see "Victoire !"
    And I reload the page
    Then I should see "Le côté Lumineux de la force"
    And "Obscure" should precede "Lumineux"

    When I select "Anakin" from the "1" select of "content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Marron"
    And I submit the widget
    Then I should see "Victoire !"
    And I reload the page
    Then I should see "Le côté Marron de la force"
    And "Marron" should precede "Lumineux"
    And "Marron" should precede "Obscure"
    When I select "Anakin" from the "3" select of "content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Jaune"
    And I submit the widget
    Then I should see "Victoire !"
    And I reload the page
    Then I should see "Jaune"
    And "Jaune" should precede "Lumineux"
    And "Obscure" should precede "Jaune"
    And "Marron" should precede "Jaune"
