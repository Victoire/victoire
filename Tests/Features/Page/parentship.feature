@mink:selenium2 @alice(Page) @reset-schema
Feature: Manage page parentship

    Background:
        Given I maximize the window
        And I am on homepage

    Scenario: I can create a child for a page
        When I follow the float action button
        And I should see "New page"
        When I follow the link containing "New page"
        And I wait 2 seconds
        Then I should see "Name"
        When I fill in "Name" with "anakin skywalker"
        Then I submit the widget
        And I should see "Successfully created page"
        And the url should match "/en/anakin-skywalker"
        When I follow the float action button
        Then I should see "New page"
        When I follow "New page"
        And I wait 2 seconds
        Then I should see "Name"
        When I fill in "Name" with "luke skywalker"
        And I select "anakin skywalker" from "Parent page"
        When I submit the widget
        Then I should see "Successfully created page"
        When I wait 2 seconds
        Then the url should match "/en/anakin-skywalker/luke-skywalker"

    Scenario: I can delete a page and his child
        Given the following Page:
            | currentLocale | name             | slug             | parent           | template |
            | en            | anakin skywalker | anakin-skywalker | home             | base     |
            | en            | luke skywalker   | luke-skywalker   | anakin-skywalker | base     |
        And I am on "/en/anakin-skywalker"
        Given I open the settings menu
        Then I should see "DELETE"
        Then I follow the link containing "DELETE"
        And I should see "This action will permanently delete this page from the database. This action is irreversible."
        And I press "YES, I WANT TO DELETE IT!"
        And I wait 2 seconds
        Given I am on "/en/anakin-skywalker/luke-skywalker"
        Then I should see "404 Not Found"
