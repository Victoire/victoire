@mink:selenium2 @alice(Page) @reset-schema
Feature: Test asynchronous widget

    Background:
        Given I maximize the window
        And I am on homepage

    Scenario: I create an asynchronous widget
        When I switch to "layout" mode
        And I should see "New content"
        And I select "Force" from the "1" select of "main_content" slot
        Then I should see "Force side"
        When I fill in "Force side" with "dark"
        And I check the "Asynchronous load?" checkbox
        And I submit the widget
        Then I should see "The dark side of the force"