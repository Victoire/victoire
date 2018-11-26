@mink:selenium2 @alice(Page) @reset-schema
Feature: Mercenary is not a BusinessEntity itself but extends Character which is one

    Background:
        Given I maximize the window
        And I am on homepage

    Scenario: I can view the mercenary show view
        Given the following Mercenaries:
            | name       | midiChlorians | slug       |
            | Boba fet   | 1500          | boba-fet   |

        And the following BusinessTemplate:
            | currentLocale |name                       | backendName  | slug                     |  businessEntity | parent  | template      |
            | en            | Character profile - {{item.name}} | Character profile | character-profile-{{item.slug}} |  Character        | home    | base |
        And the following WidgetMap:
            | view | action | slot |
            | character-profile-{{item.slug}} | create | main_content |
        And the following WidgetText:
            | widgetMap                | fields                       | mode           | businessEntity |
            | character-profile-{{item.slug}} | a:1:{s:7:"content";s:4:"name";} | businessEntity | Character             |
        And I am on "/en/victoire-dcms/business-template/show/4"
        Then I should see "Boba fet"
        And I am on "/en/character-profile-boba-fet"
        Then I should see "Boba fet"
        # TEST the virtual BEP is available in LinkExtension
        When I am on "/"
        Then I switch to "layout" mode
        And I should see "New content"
        When I select "Button" from the "1" select of "main_content" slot
        Then I should see "Widget (Button)"
        When I select "Website page" from "_a_static_widget_button[link][linkType]"
        And I should see "Choose a page"
        When I select "Character profile - Boba fet" from "_a_static_widget_button[link][viewReference]"
        And I fill in "_a_static_widget_button[title]" with "Boba fet profile"
        And I submit the widget
        When I reload the page
        Then I should see "Boba fet profile"
        When I follow "Boba fet profile"
        Then I should be on "/en/character-profile-boba-fet"
