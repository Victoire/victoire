@mink:selenium2 @alice(Page) @reset-schema
Feature: Delete a widget

    Background:
        Given I maximize the window

    Scenario: Abort Delete
        Given the following WidgetMap:
            | view | action | slot         |
            | home | create | main_content |
        Given the following WidgetForce:
            | widgetMap | side |
            | home      | dark |
        And I am on homepage
        Given I switch to "edit" mode
        And I edit the "Force" widget
        Then I should see "DELETE"
        Given I follow the link containing "DELETE"
        Then I should see "This action will permanently delete this content from the database. This action is irreversible."
        When I press "CANCEL"
        Then I should see "The dark side of the force"
        When I reload the page
        Then I should see "The dark side of the force"

    Scenario: Create and delete a widget
        And I am on homepage
        Then I switch to "layout" mode
        And I should see "New content"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Force side"
        When I fill in "Force side" with "dark"
        And I submit the widget
        Then I should see "The dark side of the force"

    Scenario: Delete a widget
        Given the following WidgetMap:
            | view | action | slot         |
            | home | create | main_content |
        Given the following WidgetForce:
            | widgetMap | side |
            | home      | dark |
        And I am on homepage
        Given I switch to "edit" mode
        And I edit the "Force" widget
        Then I should see "DELETE"
        Given I follow the link containing "DELETE"
        Then I should see "This action will permanently delete this content from the database. This action is irreversible."
        Given I press "YES, I WANT TO DELETE IT!"
        Then I wait 2 seconds
        And I should not see "The dark side of the force"
