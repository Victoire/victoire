@mink:selenium2 @alice(Page) @alice(Template) @reset-schema
Feature: Create business entity pages

    Background:
        Given the following Jedis:
            | name     | side   | midiChlorians | slug     | author             |
            | Anakin   | dark   | 20000         | anakin   | anakin@victoire.io |
            | Yoda     | bright | 17500         | yoda     | z6po@victoire.io   |
            | Kylo Ren | Double | 20000         | kylo-ren | anakin@victoire.io |
        And I maximize the window
        And I am on homepage

    Scenario: I can create a new Business entity page pattern
        When I open the additional menu drop
        Then I should see "Business Template"
        When I follow "Business Template"
        Then I should see "Jedi"
        Then I should see "ADD A BUSINESS TEMPLATE"
        When I follow the tab "Jedi"
        And I should see "ADD A BUSINESS TEMPLATE"
        And I follow "ADD A BUSINESS TEMPLATE"
        Then I should see "Create a business template"
        When I fill in "Name" with "Jedi profile - {{item.name}}"
        And I fill in "URL" with "jedi-profile-{{item.slug}}"
        And I follow "Create"
        And I wait 2 seconds
        Then the url should match "/en/victoire-dcms/business-template/show/4"
        And I should see "The business template has been successfully created"

    Scenario: I can create some content in the pattern
        Given the following BusinessTemplate:
            | currentLocale | name                         | backendName  | slug                       | businessEntityId | parent | template |
            | en            | Jedi profile - {{item.name}} | Jedi profile | jedi-profile-{{item.slug}} | jedi             | home   | base     |
        And I wait 2 seconds
        When I am on "/en/victoire-dcms/business-template/show/4"
        And I switch to "layout" mode
        Then I should see "New content"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Create"
        Then I follow the tab "Entities"
        Then I should see "Jedi"
        Then I follow the drop anchor "Jedi"
        When I open the widget mode drop for entity "Jedi"
        And I should see "Current entity"
        And I follow the drop anchor "Current entity"
        And I select "side" from "jedi_a_businessEntity_widget_force[fields][side]"
        And I submit the widget
        Then I should see "The dark side of the force"
        Given I am on "/en/jedi-profile-anakin"
        Then I should see "The dark side of the force"
        Given I am on "/en/jedi-profile-yoda"
        Then I should see "The bright side of the force"

    Scenario: I can create two Business entities page patterns differentiated by queries and access to their related Business Entity pages
        Given the following BusinessTemplate:
            | currentLocale | name                                | backendName         | slug                              | businessEntityId | parent | template | query                    |
            | en            | Jedi profile dark - {{item.name}}   | Jedi profile dark   | jedi-profile-dark-{{item.slug}}   | jedi             | home   | base     | WHERE item.side='dark'   |
            | en            | Jedi profile bright - {{item.name}} | Jedi profile bright | jedi-profile-bright-{{item.slug}} | jedi             | home   | base     | WHERE item.side='bright' |
        Given the following WidgetMap:
            | view                              | action | slot         |
            | jedi-profile-dark-{{item.slug}}   | create | main_content |
            | jedi-profile-bright-{{item.slug}} | create | main_content |
        Given the following WidgetForce:
            | widgetMap                         | side                                |
            | jedi-profile-dark-{{item.slug}}   | Static Widget - Jedi profile dark   |
            | jedi-profile-bright-{{item.slug}} | Static Widget - Jedi profile bright |
        Given I am on "/en/jedi-profile-dark-anakin"
        Then I should see "Static Widget - Jedi profile dark"
        Given I am on "/en/jedi-profile-bright-anakin"
        Then I should see "404 Not Found"
        Given I am on "/en/jedi-profile-bright-yoda"
        Then I should see "Static Widget - Jedi profile bright"
        Given I am on "/en/jedi-profile-dark-yoda"
        Then I should see "404 Not Found"

    Scenario: I can override a pattern to add some specific content
        Given the following BusinessTemplate:
            | currentLocale | name                         | backendName  | slug                       | businessEntityId | parent | template |
            | en            | Jedi profile - {{item.name}} | Jedi profile | jedi-profile-{{item.slug}} | jedi             | home   | base     |
        And I wait 2 seconds
        When I am on "/en/jedi-profile-yoda"
        And I switch to "layout" mode
        And I should see "New content"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Force side"
        When I fill in "Force side" with "new"
        And I submit the widget
        And I wait 5 seconds
        Then I should see "The new side of the force"
        Given I am on "/en/victoire-dcms/business-template/show/4"
        Then I should not see "The new side of the force"

    Scenario: I add a BusinessEntity and check if its representation is accessible
        Given the following BusinessTemplate:
            | currentLocale | name                         | backendName  | slug                       | businessEntityId | parent | template |
            | en            | Jedi profile - {{item.name}} | Jedi profile | jedi-profile-{{item.slug}} | jedi             | home   | base     |
        Given the following WidgetMap:
            | view                       | action | slot         |
            | jedi-profile-{{item.slug}} | create | main_content |
        Given the following WidgetForce:
            | widgetMap                  | fields                       | mode           | businessEntityId |
            | jedi-profile-{{item.slug}} | a:1:{s:4:"side";s:4:"side";} | businessEntity | jedi             |
        Then I am on "/en/victoire-dcms/business-template/show/4"
        Then I should see "The dark side of the force"
        Given I am on "/victoire-dcms/backend/jedi/"
        When I follow "New jedi"
        Then the url should match "/victoire-dcms/backend/jedi/new"
        And I should see "New Jedi"
        When I fill in "Name" with "Mace Windu"
        And I fill in "MidiChlorians" with "20000"
        And I fill in "Slug" with "mace-windu"
        And I select "dark" from "Force side"
        And I press "Create"
        Given I am on "/en/jedi-profile-mace-windu"
        Then I should see "The dark side of the force"

    Scenario: I can create businessPage of the same entity on different businessTemplates
        Given the following BusinessTemplate:
            | currentLocale | name                         | backendName  | slug                       | businessEntityId | parent | template | query                                                                                |
            | en            | Jedi profile - {{item.name}} | Jedi profile | jedi-profile-{{item.slug}} | jedi             | home   | base     | WHERE LOWER(item.side) LIKE LOWER('bright') OR LOWER(item.side) LIKE LOWER('double') |
            | en            | Sith profile - {{item.name}} | Sith profile | sith-profile-{{item.slug}} | jedi             | home   | base     | WHERE LOWER(item.side) LIKE LOWER('dark') OR LOWER(item.side) LIKE LOWER('double')   |
        Given the following WidgetMap:
            | view                       | action | slot         |
            | jedi-profile-{{item.slug}} | create | main_content |
            | sith-profile-{{item.slug}} | create | main_content |
        Given the following WidgetForce:
            | widgetMap                  | side   |
            | jedi-profile-{{item.slug}} | bright |
            | sith-profile-{{item.slug}} | dark   |
        Given I wait 2 seconds
        Given I am on "/en/jedi-profile-kylo-ren"
        Then I should see "The bright side of the force"
        Given I am on "/en/sith-profile-kylo-ren"
        Then I should see "The dark side of the force"

    Scenario: I can use the business author criteria
        Given the following BusinessTemplate:
            | currentLocale | name                         | backendName  | slug                       | businessEntityId | parent | template |
            | en            | Jedi profile - {{item.name}} | Jedi profile | jedi-profile-{{item.slug}} | jedi             | home   | base     |
        Given I am on "/en/victoire-dcms/business-template/show/4"
        And I switch to "layout" mode
        And I should see "New content"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Create"
        Then I follow the tab "Entities"
        Then I should see "Jedi"
        Then I follow the drop anchor "Jedi"
        When I open the widget mode drop for entity "Jedi"
        And I should see "Current entity"
        And I follow the drop anchor "Current entity"
        And I select "side" from "jedi_a_businessEntity_widget_force[fields][side]"
        And I should see "QUANTUM"
        And I open the widget quantum collapse for entity "Jedi"
        And I should see "Quantum name"
        When I fill in "jedi_a_businessEntity_widget_force[criterias][2][operator]" with "is_granted"
        And I select "BUSINESS_ENTITY_OWNER" from "jedi_a_businessEntity_widget_force[criterias][2][value]"
        And I submit the widget
        Then I wait 2 seconds
        Given I am on "/en/jedi-profile-yoda"
        Then I should see "The bright side of the force"
        And I am on "/en/jedi-profile-anakin"
        Then I should see "The dark side of the force"
        Given I login as visitor
        Given I am on "/en/jedi-profile-yoda"
        Then I should see "The bright side of the force"
        And I am on "/en/jedi-profile-anakin"
        Then I should not see "The dark side of the force"
