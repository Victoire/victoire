@mink:selenium2 @alice(Page) @reset-schema
Feature: Edit a widget

    Background:
        Given I am logged in as "anakin@victoire.io"
        And the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        And I resize the window to 1024x720
        Given I open the hamburger menu
        Then I should see "Représentation métier"
        When I follow "Représentation métier"
        Then I should see "Ajouter une représentation"
        When I follow the tab "Jedi"
        And I should see "Ajouter une représentation"
        And I follow "Ajouter une représentation"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi - {{item.name}}"
        And I fill in "Url" with "fiche-jedi-{{item.slug}}"
        And I follow "Créer"
        Then I should see "La représentation métier a bien été créé"
        And I wait 5 seconds
        And I should be on "/fr/victoire-dcms/business-template/show/5"
        Then I switch to "layout" mode

    Scenario: I can create a new Business entity page pattern, create a widget and edit this widget
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I follow "Jedi"
        And I follow "Objet courant"
        And I select "side" from "jedi_businessEntity_victoire_widget_form_force[fields][side]"
        When I submit the widget
        Then I should see "Victoire !"
        And I should see "Le Côté jedi -> side de la force"
        When I switch to "edit" mode
        And I edit the "Force" widget
        Then I should see "Widget #1 - Force"
        When I select "slug" from "jedi_businessEntity_victoire_widget_form_force[fields][side]"
        And I submit the widget
        Then I should see "Victoire !"
        And I should see "Le Côté jedi -> slug de la force"

    Scenario: I can create a new Business entity page pattern, create a static widget and edit this widget in query mode
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I fill in "Côté de la force" with "Obscure"
        When I submit the widget
        Then I should see "Victoire !"
        And I should see "Le Côté Obscure de la force"
        When I switch to "edit" mode
        And I edit the "Force" widget
        Then I should see "Widget #1 - Force"
        When I follow "Jedi"
        And I follow "Requête"
        When I select "side" from "jedi_query_victoire_widget_form_force[fields][side]"
        And I submit the widget
        Then I should see "Victoire !"
        And I should see "Le Côté obscure de la force"

    Scenario: I can edit widget for an entity with missing receiver property but field is not display
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I follow "Vaisseaux"
        And I follow "Choix"
        Then I should not see "Côté de la force"
        When I follow "Jedi"
        And I follow "Choix"
        Then I should see "Côté de la force"
