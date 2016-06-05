@mink:selenium2 @alice(Page) @reset-schema
Feature: Test widgetMap

@reset-schema
Scenario: I move up a widget
  Given the following WidgetMaps:
    | id | action | position | parent |  slot   |   view  |
    | 1  | create |          |        | content |   home  |
    | 2  | create |  after   |   1    | content |   home  |
    | 3  | create |  before  |   2    | content |   home  |
  Given the following WidgetTexts:
    | content  | mode   | widgetMap |
    | Widget 1 | static |    1       |
    | Widget 2 | static |    2       |
    | Widget 3 | static |    3       |
    And I am on the homepage
    Then I should see "Widget 1"
    When I move the widgetMap "1" "before" the widgetMap "3"
    And I wait 2 seconds
    And I reload the page
    And "Widget 1" should precede "Widget 3"

@reset-schema
Scenario: I move first a widget
  Given the following WidgetMaps:
    | id | action | position | parent |  slot   |   view  |
    | 1  | create |          |        | content |   home  |
    | 2  | create |  after   |   1    | content |   home  |
    | 3  | create |  before  |   2    | content |   home  |
  Given the following WidgetTexts:
    | content  | mode   | widgetMap |
    | Widget 1 | static |    1       |
    | Widget 2 | static |    2       |
    | Widget 3 | static |    3       |
    And I am on the homepage
    Then I should see "Widget 1"
    When I move the widgetMap "3" "after" the widgetMap ""
    And I wait 2 seconds
    And I reload the page
    And "Widget 3" should precede "Widget 1"

@reset-schema
Scenario: I move down a widget
  Given the following WidgetMaps:
    | id | action | position | parent |  slot   |   view  |
    | 1  | create |          |        | content |   home  |
    | 2  | create |  after   |   1    | content |   home  |
    | 3  | create |  before  |   2    | content |   home  |
  Given the following WidgetTexts:
    | content  | mode   | widgetMap |
    | Widget 1 | static |    1       |
    | Widget 2 | static |    2       |
    | Widget 3 | static |    3       |
    And I am on the homepage
    Then I should see "Widget 1"
    When I move the widgetMap "1" "after" the widgetMap "2"
    And I wait 2 seconds
    And I reload the page
    Then "Widget 2" should precede "Widget 1"

@reset-schema
Scenario: I move a widget under a templates one

  Given the following WidgetMaps:
    | id | action | position | parent |  slot   |   view  |
    | 1  | create |          |        | content |   home  |
    | 2  | create |  after   |   1    | content |   home  |
    | 3  | create |  before  |   2    | content |   home  |
  Given the following WidgetTexts:
    | content  | mode   | widgetMap |
    | Widget 1 | static |    1       |
    | Widget 2 | static |    2       |
    | Widget 3 | static |    3       |
    And I am on the homepage
    Then I should see "Widget 1"
    When I switch to "layout" mode
    Then I should see "Nouveau Contenu"
    When I select "Texte brut" from the "3" select of "content" slot
    Then I should see "Créer"
    When I fill in "Texte *" with "Widget 4"
    And I submit the widget
    Then I should see "Victoire !"
    And I reload the page
    And "Widget 2" should precede "Widget 4"
    And "Widget 4" should precede "Widget 3"
    Then I move the widgetMap "1" "after" the widgetMap "4"
    And I wait 2 seconds
    And I reload the page
    Then "Widget 4" should precede "Widget 1"
    Then "Widget 1" should precede "Widget 3"

@reset-schema
Scenario: I create widget in a position
    Then I switch to "layout" mode
    Then I should see "Nouveau Contenu"
    When I select "Force" from the "1" select of "content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Obscure"
    And I submit the widget
    And I wait 2 seconds
    Then I should see "Victoire !"
    And I should see "Le côté Obscure de la force"

    Then I should see "Nouveau Contenu"
    When I select "Force" from the "2" select of "content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "Lumineux"
    And I submit the widget
    Then I should see "Victoire !"
    Then I should see "Le côté Lumineux de la force"
    And "Le côté Obscure de la force" should precede "Le côté Lumineux de la force"

    Given I reload the page
    Then "Le côté Obscure de la force" should precede "Le côté Lumineux de la force"

    Then I should see "Nouveau Contenu"
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
