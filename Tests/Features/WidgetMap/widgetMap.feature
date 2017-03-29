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

    Scenario: I move first a widget from simple page
        Given the following WidgetMaps:
            | id | action | position | parent | slot         | view |
            | 1  | create |          |        | main_content | home |
            | 2  | create | after    | 1      | main_content | home |
            | 3  | create | before   | 2      | main_content | home |
        Given the following WidgetTexts:
            | content  | mode   | widgetMap |
            | Widget 1 | static | 1         |
            | Widget 2 | static | 2         |
            | Widget 3 | static | 3         |
        And I am on the homepage
        Then I should see "Widget 1"
        When I move the widgetMap "3" "before" the widgetMap "1"
        And I wait 2 seconds
        And I reload the page
        And "Widget 3" should precede "Widget 1"

    Scenario: I move up a widget from simple page
        Given the following WidgetMaps:
            | id | action | position | parent | slot         | view |
            | 1  | create |          |        | main_content | home |
            | 2  | create | after    | 1      | main_content | home |
            | 3  | create | before   | 2      | main_content | home |
        Given the following WidgetTexts:
            | content  | mode   | widgetMap |
            | Widget 1 | static | 1         |
            | Widget 2 | static | 2         |
            | Widget 3 | static | 3         |
        And I am on the homepage
        Then I should see "Widget 1"
        When I move the widgetMap "1" "before" the widgetMap "3"
        And I wait 2 seconds
        And I reload the page
        And "Widget 1" should precede "Widget 3"

    Scenario: I move down a widget from simple page
        Given the following WidgetMaps:
            | id | action | position | parent | slot         | view |
            | 1  | create |          |        | main_content | home |
            | 2  | create | after    | 1      | main_content | home |
            | 3  | create | before   | 2      | main_content | home |
        Given the following WidgetTexts:
            | content  | mode   | widgetMap |
            | Widget 1 | static | 1         |
            | Widget 2 | static | 2         |
            | Widget 3 | static | 3         |
        And I am on the homepage
        Then I should see "Widget 1"
        When I move the widgetMap "1" "after" the widgetMap "2"
        And I wait 2 seconds
        And I reload the page
        Then "Widget 2" should precede "Widget 1"

    Scenario: I add widget in a position from simple page
        Then I switch to "layout" mode
        Then I should see "New content"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Force side"
        When I fill in "Force side" with "dark"
        And I submit the widget
        And I wait 2 seconds
        And I should see "The dark side of the force"
        Then I should see "New content"
        When I select "Force" from the "2" select of "main_content" slot
        Then I should see "Force side"
        When I fill in "Force side" with "bright"
        And I submit the widget
        Then I should see "The bright side of the force"
        And "The dark side of the force" should precede "The bright side of the force"
        When I reload the page
        Then "The dark side of the force" should precede "The bright side of the force"
        Then I should see "New content"
        When I select "Force" from the "2" select of "main_content" slot
        Then I should see "Force side"
        When I fill in "Force side" with "double"
        And I submit the widget
        Then I should see "The double side of the force"
        And "The double side of the force" should precede "The bright side of the force"
        And "The dark side of the force" should precede "The double side of the force"
        Given I reload the page
        And "The double side of the force" should precede "The bright side of the force"
        And "The dark side of the force" should precede "The double side of the force"

    Scenario: I delete widget from simple page
        Given the following WidgetMaps:
            | id | action | position | parent | slot         | view |
            | 1  | create |          |        | main_content | home |
            | 2  | create | after    | 1      | main_content | home |
            | 3  | create | before   | 2      | main_content | home |
        Given the following WidgetTexts:
            | content  | mode   | widgetMap |
            | Widget 1 | static | 1         |
            | Widget 2 | static | 2         |
            | Widget 3 | static | 3         |
        And I am on the homepage
        Then I should see "Widget 1"
        When I switch to "edit" mode
        Then I edit the "Text" widget
        Then I should see "DELETE"
        Given I follow the link containing "DELETE"
        Then I should see "This action will permanently delete this content from the database. This action is irreversible."
        Given I press "YES, I WANT TO DELETE IT!"
        And I reload the page
        And "Widget 3" should precede "Widget 2"