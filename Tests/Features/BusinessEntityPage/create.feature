@mink:selenium2 @alice(Page) @alice(Template) @reset-schema
Feature: Create business entity pages

    Background:
        Given the following Jedis:
            | name   | side   | midiChlorians | slug     |       author       |
            | Anakin | dark   | 20000         | anakin   | anakin@victoire.io |
            | Yoda   | bright | 17500         | yoda     |  z6po@victoire.io  |
            | Kylo Ren | Double | 20000       | kylo-ren | anakin@victoire.io |
        And I maximize the window
        And I am on homepage

    Scenario: I can create a new Business entity page pattern
        When I open the additionals menu drop
        Then I should see "Représentation métier"
        When I follow "Représentation métier"
        Then I should see "Jedi"
        Then I should see "AJOUTER UNE REPRÉSENTATION MÉTIER"
        When I follow the tab "Jedi"
        And I should see "AJOUTER UNE REPRÉSENTATION MÉTIER"
        And I follow "AJOUTER UNE REPRÉSENTATION MÉTIER"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi - {{item.name}}"
        And I fill in "URL" with "fiche-jedi-{{item.slug}}"
        And I follow "Créer"
        And I wait 2 seconds
        Then I should be on "/fr/victoire-dcms/business-template/show/4"
        And I should see "La représentation métier a bien été créée"

    Scenario: I can create some content in the pattern
        Given the following BusinessTemplate:
            | currentLocale |name                       | backendName  | slug                    |  businessEntityId | parent  | template |
            | fr            |Fiche Jedi - {{item.name}} | Fiche Jedi  | fiche-jedi-{{item.slug}} |  jedi             | home    | base |
        Then I am on "/fr/victoire-dcms/business-template/show/4"
        Then I switch to "layout" mode
        And I should see "Nouveau contenu"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Créer"
        Then I follow the tab "Entités"
        Then I should see "Jedi"
        Then I follow the drop anchor "Jedi"
        When I open the widget mode drop for entity "Jedi"
        And I should see "Objet courant"
        And I follow the drop anchor "Objet courant"
        And I select "side" from "jedi_a_businessEntity_widget_force[fields][side]"
        And I submit the widget
        Then I should see "Le côté obscur de la force"
        Given I am on "/fr/fiche-jedi-anakin"
        Then I should see "Le côté obscur de la force"
        Given I am on "/fr/fiche-jedi-yoda"
        Then I should see "Le côté lumineux de la force"

    Scenario: I can create two Business entity page patterns differentiated by queries and access to their related Business Entity pages
        Given the following BusinessTemplate:
            | currentLocale |name                       | backendName  | slug                     |  businessEntityName | parent  | template      | query |
            | fr            |Fiche Jedi Dark - {{item.name}} | Fiche Jedi Dark  | fiche-jedi-dark-{{item.slug}} |  jedi             | home    | base | WHERE item.side='dark'|
            | fr            |Fiche Jedi Bright - {{item.name}} | Fiche Jedi Bright  | fiche-jedi-bright-{{item.slug}} |  jedi             | home    | base | WHERE item.side='bright'|
        Given the following WidgetMap:
            | view | action | slot |
            | fiche-jedi-dark-{{item.slug}} | create | main_content |
            | fiche-jedi-bright-{{item.slug}} | create | main_content |
        Given the following WidgetForce:
            | widgetMap | side |
            | fiche-jedi-dark-{{item.slug}} |  Static Widget - Fiche Jedi Dark |
            | fiche-jedi-bright-{{item.slug}} |  Static Widget - Fiche Jedi Bright |
        Given I am on "/fr/fiche-jedi-dark-anakin"
        Then I should see "Static Widget - Fiche Jedi Dark"
        Given I am on "/fr/fiche-jedi-bright-anakin"
        Then I should see "404 Not Found"
        Given I am on "/fr/fiche-jedi-bright-yoda"
        Then I should see "Static Widget - Fiche Jedi Bright"
        Given I am on "/fr/fiche-jedi-dark-yoda"
        Then I should see "404 Not Found"

    Scenario: I can override a pattern to add some specific content
        Given the following BusinessTemplate:
            | currentLocale |name                       | backendName  | slug                     |  businessEntityName | parent  | template      |
            | fr            |Fiche Jedi - {{item.name}} | Fiche Jedi   | fiche-jedi-{{item.slug}} |  jedi             | home    | base |
        Given I am on "/fr/fiche-jedi-yoda"
        And I switch to "layout" mode
        And I should see "Nouveau contenu"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Créer"
        When I fill in "Côté de la force" with "Nouveau"
        And I submit the widget
        And I wait 5 seconds
        Then I should see "Le côté Nouveau de la force"
        Given I am on "/fr/victoire-dcms/business-template/show/4"
        Then I should not see "Le côté Nouveau de la force"

    Scenario: I add a BusinessEntity and check if its representation is accessible
        Given the following BusinessTemplate:
            | currentLocale |name                       | backendName  | slug                     |  businessEntityName | parent  | template      |
            | fr            |Fiche Jedi - {{item.name}} | Fiche Jedi   | fiche-jedi-{{item.slug}} |  jedi             | home    | base |
        Given the following WidgetMap:
            | view | action | slot |
            | fiche-jedi-{{item.slug}} | create | main_content |
        Given the following WidgetForce:
            | widgetMap                | fields                       | mode           | businessEntityName |
            | fiche-jedi-{{item.slug}} | a:1:{s:4:"side";s:4:"side";} | businessEntity | jedi             |
        Then I am on "/fr/victoire-dcms/business-template/show/4"
        Then I should see "Le côté obscur de la force"
        Given I am on "/victoire-dcms/backend/jedi/"
        When I follow "Nouveau jedi"
        Then I should be on "/victoire-dcms/backend/jedi/new"
        And I should see "Nouveau Jedi"
        When I fill in "Nom" with "Mace Windu"
        And I fill in "MediChloriens" with "20000"
        And I fill in "Identifiant" with "mace-windu"
        And I select "obscur" from "Côté de la force"
        And I press "Créer"
        Given I am on "/fr/fiche-jedi-mace-windu"
        Then I should see "Le côté obscur de la force"

    Scenario: I can create businessPage of the same entity on different businessTemplates
        Given the following BusinessTemplate:
            | currentLocale |name                       | backendName  | slug                     |  businessEntityName | parent  | template      | query |
            | fr            |Fiche Jedi - {{item.name}} | Fiche Jedi   | fiche-jedi-{{item.slug}} |  jedi             | home    | base | WHERE LOWER(item.side) LIKE LOWER('bright') OR LOWER(item.side) LIKE LOWER('double') |
            | fr            |Fiche Sith - {{item.name}} | Fiche Sith   | fiche-sith-{{item.slug}} |  jedi             | home    | base | WHERE LOWER(item.side) LIKE LOWER('dark') OR LOWER(item.side) LIKE LOWER('double') |
        Given the following WidgetMap:
            | view | action | slot |
            | fiche-jedi-{{item.slug}} | create | main_content |
            | fiche-sith-{{item.slug}} | create | main_content |
        Given the following WidgetForce:
            | widgetMap                |  side   |
            | fiche-jedi-{{item.slug}} |  Bright |
            | fiche-sith-{{item.slug}} |  Dark   |
        Given I wait 2 seconds
        Given I am on "/fr/fiche-jedi-kylo-ren"
        Then I should see "Le côté Bright de la force"
        Given I am on "/fr/fiche-sith-kylo-ren"
        Then I should see "Le côté Dark de la force"

    Scenario: I can use the business author criteria
        Given the following BusinessTemplate:
            | currentLocale |name                       | backendName  | slug                     |  businessEntityName | parent  | template      |
            | fr            |Fiche Jedi - {{item.name}} | Fiche Jedi   | fiche-jedi-{{item.slug}} |  jedi             | home    | base |
        Given I am on "/fr/victoire-dcms/business-template/show/4"
        And I switch to "layout" mode
        And I should see "Nouveau contenu"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Créer"
        Then I follow the tab "Entités"
        Then I should see "Jedi"
        Then I follow the drop anchor "Jedi"
        When I open the widget mode drop for entity "Jedi"
        And I should see "Objet courant"
        And I follow the drop anchor "Objet courant"
        And I select "side" from "jedi_a_businessEntity_widget_force[fields][side]"
        And I should see "QUANTUM"
        And I open the widget quantum collapse for entity "Jedi"
        And I should see "Nom du quantum"
        When I fill in "jedi_a_businessEntity_widget_force[criterias][2][operator]" with "is_granted"
        And I select "BUSINESS_ENTITY_OWNER" from "jedi_a_businessEntity_widget_force[criterias][2][value]"
        And I submit the widget
        Then I wait 2 seconds
        Given I am on "/fr/fiche-jedi-yoda"
        Then I should see "Le côté lumineux de la force"
        And I am on "/fr/fiche-jedi-anakin"
        Then I should see "Le côté obscur de la force"
        Given I login as visitor
        Given I am on "/fr/fiche-jedi-yoda"
        Then I should see "Le côté lumineux de la force"
        And I am on "/fr/fiche-jedi-anakin"
        Then I should not see "Le côté obscur de la force"
