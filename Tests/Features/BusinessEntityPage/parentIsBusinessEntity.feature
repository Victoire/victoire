@mink:selenium2 @alice(Page) @reset-schema
Feature: Mercenary is not a BusinessEntity itself but extends Character which is one

    Background:
        Given I maximize the window
        And I am on homepage

    Scenario: I can view the mercenary show view
        Given the following Mercenaries:
            | name     | midiChlorians | slug     |
            | Boba fet | 1500          | boba-fet |
        And the following BusinessTemplate:
            | currentLocale | name                              | backendName       | slug                            | businessEntityId | parent | template |
            | en            | Character profile - {{item.name}} | Character profile | character-profile-{{item.slug}} | character        | home   | base     |
        And the following WidgetMap:
            | view                            | action | slot         |
            | character-profile-{{item.slug}} | create | main_content |
        And the following WidgetText:
            | widgetMap                       | fields                          | mode           | businessEntityId |
            | character-profile-{{item.slug}} | a:1:{s:7:"content";s:4:"name";} | businessEntity | character        |
        And I am on "/en/victoire-dcms/business-template/show/4"
        Then I should see "Boba fet"
        And I am on "/en/character-profile-boba-fet"
        Then I should see "Boba fet"
