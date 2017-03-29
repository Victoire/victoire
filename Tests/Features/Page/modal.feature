@mink:selenium2 @alice(Page) @reset-schema
Feature: Display a page's content in a modal

    Background:
        Given I maximize the window
        And I am on homepage

    Scenario: I can create a modal link to a page
        Given I switch to "layout" mode
        And I should see "New content"
        When I select "Button" from the "1" select of "main_content" slot
        And I wait 2 seconds
        Then I should see "Label"
        # Add modal button
        When I fill in "Label" with "Render test in a modal"
        And I select "Website page" from "Link type"
        Then I should see "Choose a page"
        And I should not see "Modal style"
        When I select "└── English test" from "Website page"
        And I select "In a new modal window" from "Link target"
        Then I should see "Modal style"
        When I select "My custom modal" from "Modal style"
        And I submit the widget
        And I should see "Render test in a modal"
        # Add Force widget in test page
        When I am on "/en/english-test"
        And I switch to "layout" mode
        Then I should see "New content"
        And I select "Force" from the "1" select of "main_content" slot
        Then I should see "Force side"
        When I fill in "Force side" with "dark"
        And I submit the widget
        Then I should see "The dark side of the force"
        # Test Force widget rendering in modal
        When I am on homepage
        And I follow "Render test in a modal"
        Then I should see "My Custom modal"
        Then I should not see "The dark side of the force"
        # Change modal style
        When I am on homepage
        And I switch to "edit" mode
        And I edit the "Button" widget
        Then I should see "Modal style"
        When I select "Default" from "Modal style"
        And I submit the widget
        Then I should see "Render test in a modal"
        When I switch to "readonly" mode
        And I follow "Render test in a modal"
        Then I should not see "My Custom modal"
        And I should see "The dark side of the force"
