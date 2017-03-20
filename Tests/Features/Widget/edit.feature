@mink:selenium2 @alice(Page) @reset-schema
Feature: Edit a widget

    Background:
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        Given the following BusinessTemplate:
            | currentLocale |name                       | backendName  | slug                    |  businessEntityId | parent  | template |
            | fr            |Fiche Jedi - {{item.name}} | Fiche Jedi  | fiche-jedi-{{item.slug}} |  jedi             | home    | base |
        And I maximize the window

    Scenario: I can create a new Business entity page pattern, create a widget and edit this widget
        Given the following WidgetMap:
            | view | action | slot |
            | fiche-jedi-{{item.slug}} | create | main_content |
        Given the following WidgetForce:
            | widgetMap                | fields                       | mode           | businessEntityId |
            | fiche-jedi-{{item.slug}} | a:1:{s:4:"side";s:4:"side";} | businessEntity | jedi             |
        Given I am on "/fr/victoire-dcms/business-template/show/4"
        And I should see "Le côté obscur de la force"
        When I switch to "edit" mode
        And I edit the "Force" widget
        Then I should see "Mettre à jour"
        When I select "slug" from "jedi_a_businessEntity_widget_force[fields][side]"
        And I submit the widget
        And I should see "Le côté anakin de la force"

    Scenario: I can create a new Business entity page pattern, create a static widget and edit this widget in query mode
        Given the following WidgetMap:
            | view | action | slot |
            |  fiche-jedi-{{item.slug}}| create | main_content |
        Given the following WidgetForce:
            | widgetMap                | side |
            | fiche-jedi-{{item.slug}} | Obscur |
        Given I am on "/fr/victoire-dcms/business-template/show/4"
        And I should see "Le côté Obscur de la force"
        When I switch to "edit" mode
        And I edit the "Force" widget
        Then I should see "Mettre à jour"
        When I follow the tab "Jedi"
        And I should see "Requête"
        And I follow "Requête"
        When I select "side" from "jedi_a_query_widget_force[fields][side]"
        And I submit the widget
        And I should see "Le côté obscur de la force"

    Scenario: I cannot edit widget for an entity with missing business parameter
        Given I am on "/fr/victoire-dcms/business-template/show/4"
        When I switch to "layout" mode
        Then I should see "Nouveau contenu"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Créer"
        When I follow the tab "Entités"
        And I should see disable tab "Vaisseau Spatial"

    Scenario: I can edit the original widget from a child page
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | Dark   | 20000         | anakin |
        Given the following WidgetMap:
            | view | action | slot |
            | fiche-jedi-{{item.slug}} | create | main_content |
        Given the following WidgetForce:
            | widgetMap                | side |
            | fiche-jedi-{{item.slug}} | Obscur |
        Given I am on "/fr/victoire-dcms/business-template/show/4"
        And I should see "Le côté Obscur de la force"
        Given I am on "/fr/fiche-jedi-anakin"
        And I should see "Le côté Obscur de la force"
        When I switch to "edit" mode
        And I edit the "Force" widget
        And I wait 3 seconds
        Then I should see "Attention !"
        And I should see "Ce contenu appartient à un modèle parent"
        And I follow "modifier le contenu original"
        And I wait 5 seconds
        Then I should not see "Attention !"
        And I should not see "Ce contenu appartient à un modèle parent"
        When I fill in "Côté de la force" with "Dark"
        And I submit the widget
        Given I am on "/fr/victoire-dcms/business-template/show/4"
        Then I should see "Le côté Dark de la force"

