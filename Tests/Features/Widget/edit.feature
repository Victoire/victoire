@mink:selenium2 @alice(Page) @reset-schema
Feature: Edit a widget

    Background:
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        Given the following BusinessTemplate:
            | currentLocale | name                         | backendName  | slug                       | businessEntity   | parent | template |
            | en            | Jedi profile - {{item.name}} | Jedi profile | jedi-profile-{{item.slug}} | jedi             | home   | base     |
        And I maximize the window

    Scenario: I can create a new Business entity page pattern, create a widget and edit this widget
        Given the following WidgetMap:
            | view                       | action | slot         |
            | jedi-profile-{{item.slug}} | create | main_content |
        Given the following WidgetForce:
            | widgetMap                  | fields                       | mode           | businessEntity   |
            | jedi-profile-{{item.slug}} | a:1:{s:4:"side";s:4:"side";} | businessEntity | jedi             |
        Given I am on "/en/victoire-dcms/business-template/show/4"
        And I should see "The dark side of the force"
        When I switch to "edit" mode
        And I edit the "Force" widget
        Then I should see "UPDATE"
        When I select "slug" from "jedi_a_businessEntity_widget_force[fields][side]"
        And I submit the widget
        And I should see "The anakin side of the force"

    Scenario: I can create a new Business entity page pattern, create a static widget and edit this widget in query mode
        Given the following WidgetMap:
            | view                       | action | slot         |
            | jedi-profile-{{item.slug}} | create | main_content |
        And the following WidgetForce:
            | widgetMap                  | side |
            | jedi-profile-{{item.slug}} | dark |
        And I am on "/en/victoire-dcms/business-template/show/4"
        Then I should see "The dark side of the force"
        When I switch to "edit" mode
        And I edit the "Force" widget
        Then I should see "UPDATE"
        When I follow the tab "Entities"
        Then I should see "Jedi"
        When I follow the drop anchor "Jedi"
        And I open the widget mode drop for entity "Jedi"
        Then I should see "Query"
        When I follow the drop anchor "Query"
        And I select "side" from "jedi_a_query_widget_force[fields][side]"
        And I submit the widget
        Then I should see "The dark side of the force"

    Scenario: I cannot edit widget for an entity with missing business parameter
        Given I am on "/en/victoire-dcms/business-template/show/4"
        When I switch to "layout" mode
        Then I should see "New content"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Create"
        When I follow the tab "Entities"
        And I should see disable drop anchor "Spaceship"

    Scenario: I can edit the original widget from a child page
        Given the following Jedis:
            | name   | side | midiChlorians | slug   |
            | Anakin | dark | 20000         | anakin |
        Given the following WidgetMap:
            | view                       | action | slot         |
            | jedi-profile-{{item.slug}} | create | main_content |
        Given the following WidgetForce:
            | widgetMap                  | side |
            | jedi-profile-{{item.slug}} | dark |
        Given I am on "/en/victoire-dcms/business-template/show/4"
        And I should see "The dark side of the force"
        Given I am on "/en/jedi-profile-anakin"
        And I should see "The dark side of the force"
        When I switch to "edit" mode
        And I edit the "Force" widget
        And I wait 3 seconds
        Then I should see "Warning!"
        And I should see "This content is owned by a parent template"
        And I should see "EDIT THE ORIGINAL CONTENT"
        When I follow "EDIT THE ORIGINAL CONTENT"
        And I wait 5 seconds
        Then I should not see "Warning!"
        And I should not see "This content is owned by a parent template"
        When I fill in "Force side" with "dark"
        And I submit the widget
        Given I am on "/en/victoire-dcms/business-template/show/4"
        Then I should see "The dark side of the force"

