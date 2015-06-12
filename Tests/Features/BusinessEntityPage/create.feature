@mink:selenium2 @alice(Page) @reset-schema
Feature: Create business entity pages

    Background:
        Given I am logged in as "anakin@victoire.io"
        And the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        And I maximize the window

    Scenario: I can create a new Business entity page pattern a create some content in the pattern
        Given I open the hamburger menu
        Then I should see "Représentation métier"
        When I follow "Représentation métier"
        Then I should see "Ajouter une représentation"
        When I follow the tab "jedi"
        And I follow "Ajouter une représentation"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi - {{item.name}}"
        And I fill in "Url" with "fiche-jedi-{{item.slug}}"
        And I follow "Créer"
        Then I should see "La représentation métier a bien été créé"
        And I wait 5 seconds
        And I should be on "/fr/fiche-jedi-%7B%7Bitem.slug%7D%7D"
        Then I switch to "layout" mode
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I follow "Jedi"
        And I follow "Objet courant"
        And I select "side" from "jedi_businessEntity_victoire_widget_form_force[fields][side]"
        And I submit the widget
        Then I should see "Victoire !"
        Then I should see "Le Côté jedi -> side de la force"
        Given I am on "/fr/fiche-jedi-anakin"
        Then I should see "Le Côté dark de la force"
        Given I am on "/fr/fiche-jedi-yoda"
        Then I should see "Le Côté bright de la force"

    Scenario: I can override a pattern to add some specific content
        Given I open the hamburger menu
        Then I should see "Représentation métier"
        When I follow "Représentation métier"
        Then I should see "Ajouter une représentation"
        When I follow the tab "jedi"
        And I follow "Ajouter une représentation"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi - {{item.name}}"
        And I fill in "Url" with "fiche-jedi-{{item.slug}}"
        And I follow "Créer"
        Then I should see "La représentation métier a bien été créé"
        Given I am on "/fr/fiche-jedi-yoda"
        And I switch to "layout" mode
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I fill in "Côté de la force" with "Nouveau"
        And I submit the widget
        Then I should see "Le Côté Nouveau de la force"
        Given I am on "/fr/fiche-jedi-%7B%7Bitem.slug%7D%7D"
        Then I should not see "Le Côté Nouveau de la force"

    Scenario: I add a BusinessEntity and check its representation is accessible
        Given I open the hamburger menu
        Then I should see "Représentation métier"
        When I follow "Représentation métier"
        Then I should see "Ajouter une représentation"
        When I follow the tab "jedi"
        And I follow "Ajouter une représentation"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi - {{item.name}}"
        And I fill in "Url" with "fiche-jedi-{{item.slug}}"
        And I follow "Créer"
        Then I should see "La représentation métier a bien été créé"
        And I should be on "/fr/fiche-jedi-%7B%7Bitem.slug%7D%7D"
        Then I switch to "layout" mode
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I follow the tab "Jedi"
        And I follow the tab "Objet courant"
        And I select "side" from "jedi_businessEntity_victoire_widget_form_force[fields][side]"
        And I submit the widget
        Then I should see "Victoire !"
        Then I should see "Le Côté jedi -> side de la force"

        Given I am on "/victoire-dcms/backend/jedi/"
        When I follow "Nouveau jedi"
        Then I should be on "/victoire-dcms/backend/jedi/new"
        And I should see "Nouveau Jedi"
        When I fill in "Nom" with "Mace Windu"
        And I fill in "MediChloriens" with "20000"
        And I fill in "Identifiant" with "mace-windu"
        And I select "Obscure" from "Coté de la force"
        And I press "Créer"

        Given I am on "/fr/fiche-jedi-mace-windu"
        Then I should see "Le Côté dark de la force"



