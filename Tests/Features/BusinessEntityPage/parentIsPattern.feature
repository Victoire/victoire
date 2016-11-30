@mink:selenium2 @alice(Page) @reset-schema
Feature: Mercenary is not a BusinessEntity itself but extends Character which is one

    Background:
        Given I maximize the window
        And I am on homepage

    Scenario: I can view the mercenary show view
        Given the following Mercenaries:
            | name       | midiChlorians | slug       |
            | Boba fet   | 1500          | boba-fet   |

        Given I open the hamburger menu
        Given the following BusinessTemplate:
            | currentLocale |name                       | backendName  | slug                     |  businessEntityName | parent  | template      |
            | fr            |Fiche Personnage - {{item.name}} | Fiche Jedi   | fiche-personnage-{{item.slug}} |  character        | home    | base |
        Given the following WidgetMap:
            | view | action | slot |
            | fiche-personnage-{{item.slug}} | create | main_content |
        Given the following WidgetText:
            | widgetMap                | fields                       | mode           | businessEntityName |
            | fiche-personnage-{{item.slug}} | a:1:{s:7:"content";s:4:"name";} | businessEntity | character             |
        Given I am on "/fr/victoire-dcms/business-template/show/4"
            Then I should see "Boba fet"
        Given I am on "/fr/fiche-personnage-boba-fet"
            Then I should see "Boba fet"

        # TEST the virtual BEP is available in LinkExtension
        Given I am on "/"
            Then I switch to "layout" mode
            And I should see "Nouveau contenu"
            When I select "Bouton" from the "1" select of "main_content" slot
            Then I should see "Créer"
            When I select "Page" from "_a_static_widget_button[link][linkType]"
            And I should see "Choisissez une page"
            When I select "Fiche Personnage - Boba fet" from "_a_static_widget_button[link][viewReference]"
            And I fill in "_a_static_widget_button[title]" with "Fiche de Boba fet"
            And I submit the widget
            Given I reload the page
            Then I should see "Fiche de Boba fet"
            When I follow "Fiche de Boba fet"
            Then I should be on "/fr/fiche-personnage-boba-fet"
