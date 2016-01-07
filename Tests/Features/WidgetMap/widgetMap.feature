@mink:selenium2 @alice(Page) @reset-schema
Feature: Test widgetMap

Background:
    Given I am logged in as "anakin@victoire.io"

@reset-schema
Scenario: I move up a widget
  Given the following WidgetTexts:
    | content  | mode   |
    | Widget 1 | static |
    | Widget 2 | static |
    | Widget 3 | static |
  Given the following WidgetMaps:
    | id |  widget  | action | left | right |  slot   |   view  |
    | 1  | Widget 1 | create |      |       | content |   home  |
    | 2  | Widget 2 | create |      |   1   | content |   home  |
    | 3  | Widget 3 | create |  2   |       | content |   home  |
    And I should see "Widget 1"
    Then I move the widget "Widget 3" under the widget "Widget 1"
    Then I wait 2 seconds
    And I reload the page
    And "Widget 3" should precede "Widget 2"

@reset-schema
Scenario: I move first a widget
  Given the following WidgetTexts:
    | content  | mode   |
    | Widget 1 | static |
    | Widget 2 | static |
    | Widget 3 | static |
  Given the following WidgetMaps:
    | id |  widget  | action | left | right |  slot   |   view  |
    | 1  | Widget 1 | create |      |       | content |   home  |
    | 2  | Widget 2 | create |      |   1   | content |   home  |
    | 3  | Widget 3 | create |  2   |       | content |   home  |
    And I should see "Widget 1"
    Then I move the widget "Widget 3" under the widget ""
    Then I wait 2 seconds
    And I reload the page
    And "Widget 3" should precede "Widget 1"

@reset-schema
Scenario: I move down a widget
  Given the following WidgetTexts:
    | content  | mode   |
    | Widget 1 | static |
    | Widget 2 | static |
    | Widget 3 | static |
  Given the following WidgetMaps:
    | id |  widget  | action | left | right |  slot   |   view  |
    | 1  | Widget 1 | create |      |       | content |   home  |
    | 2  | Widget 2 | create |      |   1   | content |   home  |
    | 3  | Widget 3 | create |  2   |       | content |   home  |
    And I should see "Widget 1"
    Then I move the widget "Widget 1" under the widget "Widget 2"
    Then I wait 2 seconds
    And I reload the page
    And "Widget 2" should precede "Widget 1"

@reset-schema
Scenario: I move a widget under a templates one

  Given the following WidgetTexts:
    | content  | mode   |
    | Widget 1 | static |
    | Widget 2 | static |
    | Widget 3 | static |
  Given the following WidgetMaps:
    | id |  widget  | action | left | right |  slot   |   view  |
    | 1  | Widget 1 | create |      |       | content |   home  |
    | 2  | Widget 2 | create |      |   1   | content |   home  |
    | 3  | Widget 3 | create |  2   |       | content |   home  |
    And I should see "Widget 1"
    Then I switch to "layout" mode
    When I select "Texte brut" from the "3" select of "content" slot
    Then I should see "Créer"
    When I fill in "Texte *" with "Widget 4"
    And I submit the widget
    Then I should see "Victoire !"
    And I reload the page
    Then I wait 5 seconds
    And "Widget 2" should precede "Widget 4"
    And "Widget 4" should precede "Widget 3"
    Then I move the widget "Widget 1" under the widget "Widget 4"
    Then I wait 2 seconds
    And I reload the page
    Then I wait 5 seconds
    And "Widget 4" should precede "Widget 1"
    And "Widget 1" should precede "Widget 3"

@reset-schema
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
    And "Le côté Double de la force" should precede "Le côté Lumineux de la force"
    And "Le côté Obscure de la force" should precede "Le côté Double de la force"

    Given I reload the page
    And "Le côté Double de la force" should precede "Le côté Lumineux de la force"
    And "Le côté Obscure de la force" should precede "Le côté Double de la force"
