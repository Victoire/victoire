@mink:selenium2 @alice(Page) @reset-schema
Feature: Delete a page

    Background:
        Given I maximize the window
        And I am on homepage

    Scenario: I can delete a new page
        Given I am on "/en/english-test"
        And I open the settings menu
        Then I should see "DELETE"
        When I follow the link containing "DELETE"
        Then I should see "This action will permanently delete this page from the database. This action is irreversible."
        When I press "YES, I WANT TO DELETE IT!"
        Then I should be on "/en/"
        When I am on "/en/english-test"
        Then I should see "404 Not Found"