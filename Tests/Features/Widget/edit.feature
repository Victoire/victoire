@mink:selenium2 @alice(Page) @reset-schema
Feature: Edit a widget

    Background:
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        And I maximize the window
        And I am on homepage
        Given I open the hamburger menu
        Then I should see "Représentation métier"
        When I follow "Représentation métier"
        Then I should see "Ajouter une représentation métier"
        When I follow the tab "Jedi"
        And I should see "Ajouter une représentation métier"
        And I follow "Ajouter une représentation métier"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi - {{item.name}}"
        And I fill in "URL" with "fiche-jedi-{{item.slug}}"
        And I follow "Créer"
        And I wait 5 seconds
        Then I should be on "/fr/victoire-dcms/business-template/show/5"
        And I should see "La représentation métier a bien été créée"
        Then I switch to "layout" mode
        And I should see "Nouveau contenu"

    Scenario: I can create a new Business entity page pattern, create a widget and edit this widget
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I follow the tab "Jedi"
        And I should see "Objet courant"
        And I follow "Objet courant"
        And I select "side" from "jedi_a_businessEntity_widget_force[fields][side]"
        When I submit the widget
        Then I should see "Victoire !"
        And I should see "Le Côté obscure de la force"
        When I switch to "edit" mode
        And I edit the "Force" widget
        Then I should see "Mettre à jour"
        When I select "slug" from "jedi_a_businessEntity_widget_force[fields][side]"
        And I submit the widget
        Then I should see "Victoire !"
        And I should see "Le Côté anakin de la force"

    Scenario: I can create a new Business entity page pattern, create a static widget and edit this widget in query mode
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I fill in "Côté de la force" with "Obscure"
        When I submit the widget
        Then I should see "Victoire !"
        And I should see "Le Côté Obscure de la force"
        When I switch to "edit" mode
        And I edit the "Force" widget
        Then I should see "Mettre à jour"
        When I follow the tab "Jedi"
        And I should see "Requête"
        And I follow "Requête"
        When I select "side" from "jedi_a_query_widget_force[fields][side]"
        And I submit the widget
        Then I should see "Victoire !"
        And I should see "Le Côté obscure de la force"

    Scenario: I cannot edit widget for an entity with missing business parameter
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        And I should see disable tab "Vaisseaux"

    Scenario: I can edit the original widget from a child page
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | Dark   | 20000         | anakin |
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I fill in "Côté de la force" with "Obscure"
        When I submit the widget
        Then I should see "Victoire !"
        And I should see "Le Côté Obscure de la force"
        Given I am on "/fr/fiche-jedi-anakin"
        When I switch to "edit" mode
        And I edit the "Force" widget
        Then I should see "Attention ! Ce contenu appartient à un modèle parent"
        And I follow "modifier le contenu original"
        And I wait 5 seconds
        Then I should not see "Attention ! Ce contenu appartient à un modèle parent"
        When I fill in "Côté de la force" with "Dark"
        And I submit the widget
        Then I should see "Victoire"
        Given I am on "/fr/victoire-dcms/business-template/show/5"
        Then I should see "Le Côté Dark de la force"

