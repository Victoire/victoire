@mink:selenium2 @alice(Page) @reset-schema
Feature: Delete Business Entity pages

    Background:
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        And I maximize the window
        And I am on homepage

    Scenario: I can delete a Business Entity and its Business Entity page
        Given the following BusinessTemplate:
            | currentLocale | name                         | backendName  | slug                       | businessEntity | parent | template |
            | en            | Jedi profile - {{item.name}} | Jedi profile | jedi-profile-{{item.slug}} | Jedi           | home   | base     |
        When I am on "/en/jedi-profile-anakin"
        And I switch to "layout" mode
        And I should see "New content"
        And I select "Force" from the "1" select of "main_content" slot
        Then I should see "Force side"
        When I fill in "Force side" with "new"
        And I submit the widget
        And I wait 5 seconds
        Then I should see "The new side of the force"
        When I am on "/victoire-dcms/backend/jedi/1/edit"
        And I press "Delete"
        Then I should be on "victoire-dcms/backend/jedi/"
        When I am on "/en/jedi-profile-anakin"
        Then I should see "404 Not Found"