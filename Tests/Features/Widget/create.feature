@mink:selenium2 @alice(Page) @reset-schema
Feature: Create a widget

    Background:
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        And I maximize the window

    Scenario: I create a simple widget
        Given I am on the homepage
        When I switch to "layout" mode
        And I should see "New content"
        And I select "Plain Text" from the "1" select of "main_content" slot
        Then I should see "Widget (Plain Text)"
        When I fill in "_a_static_widget_text[content]" with "test"
        And I submit the widget
        Then I should see "test"

    Scenario: I create a picker widget
        Given I am on the homepage
        When I switch to "layout" mode
        And I should see "New content"
        And I select "Plain Text" from the "1" select of "main_content" slot
        Then I should see "Text"
        Then I follow the tab "Entities"
        Then I should see "Jedi"
        Then I follow the drop anchor "Jedi"
        And I should see "Select"
        And I select "side" from "jedi_a_entity_widget_text[fields][content]"
        And I select "Anakin" from "jedi_a_entity_widget_text[entity_proxy][jedi]"
        And I submit the widget
        Then I should see "dark"

    Scenario: I create an api widget
        Given I am on the homepage
        When I switch to "layout" mode
        And I should see "New content"
        And I select "Plain Text" from the "1" select of "main_content" slot
        Then I should see "Create"
        Then I follow the tab "Entités"
        Then I should see "Users"
        Then I follow the drop anchor "Users"
        And I should see "Select"
        And I select "email" from "users_a_entity_widget_text[fields][content]"
        And I fill in select2 input "users_a_entity_widget_text[entity_proxy][ressourceId]" with "ervin" and select "Ervin Howell"
        And I submit the widget
        Then I should see "Shanna@melissa.tv"
