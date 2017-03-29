@mink:selenium2 @alice(Page) @reset-schema
Feature: Create a page

    Background:
        Given I maximize the window
        And I am on homepage

    Scenario: I can create a new page
        When I follow the float action button
        Then I should see "New page"
        And I follow "New page"
        And I wait 2 seconds
        And I should see "Name"
        When I fill in "Name" with "tatooine"
        Then I submit the widget
        And I wait 3 second
        And I should see "Successfully created page"
        And I should be on "/en/tatooine"

    @alice(Template)
    Scenario: I can change the name and the url of a given page
        Given the following Page:
            | currentLocale | name     | slug     | parent | template |
            | en            | tatooine | tatooine | home   | base     |
        And I am on "/en/tatooine"
        And I open the settings menu
        And I should see "UPDATE"
        Then I fill in "Name" with "anoth"
        Then I fill in "page_settings_translations_en_slug" with "anoth"
        And I submit the widget
        And I wait 5 seconds
        Then I should be on "/en/anoth"
        And I should see "Successfully modified page"
