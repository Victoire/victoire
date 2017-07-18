@mink:selenium2 @alice(Page) @alice(Template) @reset-schema
Feature: Test widgetMap
# Ececuted tests:
#  On a simple page:
#    - add
#    - delete
#    - move

  Background:
    Given I maximize the window
    And I am on homepage
@reset-schema
Scenario: I move first a widget from simple page
  Given the following WidgetMaps:
    | id | action | position | parent |  slot        |   view  |
    | 1  | create |          |        | main_content |   home  |
    | 2  | create |  after   |   1    | main_content |   home  |
    | 3  | create |  before  |   2    | main_content |   home  |
  Given the following WidgetTexts:
    | content  | mode   | widgetMap |
    | Widget 1 | static |    1       |
    | Widget 2 | static |    2       |
    | Widget 3 | static |    3       |
  And I am on the homepage
  Then I should see "Widget 1"
  When I move the widgetMap "3" "before" the widgetMap "1"
  And I wait 2 seconds
  And I reload the page
  And "Widget 3" should precede "Widget 1"

@reset-schema
Scenario: I move up a widget from simple page
  Given the following WidgetMaps:
    | id | action | position | parent |  slot        |   view  |
    | 1  | create |          |        | main_content |   home  |
    | 2  | create |  after   |   1    | main_content |   home  |
    | 3  | create |  before  |   2    | main_content |   home  |
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
Scenario: I move down a widget from simple page
  Given the following WidgetMaps:
    | id | action | position | parent | slot         |   view  |
    | 1  | create |          |        | main_content |   home  |
    | 2  | create |  after   |   1    | main_content |   home  |
    | 3  | create |  before  |   2    | main_content |   home  |
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
Scenario: I add widget in a position from simple page
  Then I switch to "layout" mode
  Then I should see "Nouveau contenu"
  When I select "Force" from the "1" select of "main_content" slot
  Then I should see "Créer"
  When I fill in "Côté de la force" with "obscur"
  And I submit the widget
  And I wait 2 seconds
  And I should see "Le côté obscur de la force"

  Then I should see "Nouveau contenu"
  When I select "Force" from the "2" select of "main_content" slot
  Then I should see "Créer"
  When I fill in "Côté de la force" with "Lumineux"
  And I submit the widget
  Then I should see "Le côté Lumineux de la force"
  And "Le côté obscur de la force" should precede "Le côté Lumineux de la force"

  Given I reload the page
  Then "Le côté obscur de la force" should precede "Le côté Lumineux de la force"

  Then I should see "Nouveau contenu"
  Given I select "Force" from the "2" select of "main_content" slot
  Then I should see "Créer"
  When I fill in "Côté de la force" with "Double"
  And I submit the widget
  Then I should see "Le côté Double de la force"
  And "Le côté Double de la force" should precede "Le côté Lumineux de la force"
  And "Le côté obscur de la force" should precede "Le côté Double de la force"

  Given I reload the page
  And "Le côté Double de la force" should precede "Le côté Lumineux de la force"
  And "Le côté obscur de la force" should precede "Le côté Double de la force"

@reset-schema
Scenario: I delete widget from simple page
  Given the following WidgetMaps:
    | id | action | position | parent | slot         |   view  |
    | 1  | create |          |        | main_content |   home  |
    | 2  | create |  after   |   1    | main_content |   home  |
    | 3  | create |  before  |   2    | main_content |   home  |
  Given the following WidgetTexts:
    | content  | mode   | widgetMap |
    | Widget 1 | static |    1       |
    | Widget 2 | static |    2       |
    | Widget 3 | static |    3       |
  And I am on the homepage
  Then I should see "Widget 1"
  When I switch to "edit" mode
  And I edit the "Text" widget
  Then I should see "Supprimer"
  Given I follow "Supprimer"
  Then I should see "Cette action va définitivement supprimer ce contenu. Cette action est irréversible."
  And I should see "Êtes-vous sûr ?"
  Given I press "J'ai bien compris, je confirme la suppression"
  And I reload the page
  And "Widget 3" should precede "Widget 2"
