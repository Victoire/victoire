@mink:selenium2 @alice(Page) @reset-schema
Feature: Create business entity pages

    Background:
        Given I am logged in as "anakin@victoire.io"
        And the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        And I resize the window to 1024x720

    @smartStep
    Scenario: I can create a new Business entity page pattern and create some content in the pattern
        Given I open the hamburger menu
        Then I should see "Représentation métier"
        When I follow "Représentation métier"
        And I close the hamburger menu
        Then I should see "Jedi"
        Then I should see "Ajouter une représentation métier"
        When I follow the tab "Jedi"
        And I should see "Ajouter une représentation métier"
        And I follow "Ajouter une représentation métier"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi - {{item.name}}"
        And I fill in "URL" with "fiche-jedi-{{item.slug}}"
        And I follow "Créer"
        Then I should see "La représentation métier a bien été créée"
        And I wait 5 seconds
        And I should be on "/fr/victoire-dcms/business-template/show/5"
        Then I switch to "layout" mode
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        Then I follow the tab "Jedi"
        And I follow "Objet courant"
        And I select "side" from "jedi_businessEntity_victoire_widget_form_force[fields][side]"
        And I submit the widget
        Then I should see "Victoire !"
        Then I should see "Le Côté jedi -> side de la force"
        Given I am on "/fr/fiche-jedi-anakin"
        Then I should see "Le Côté obscure de la force"
        Given I am on "/fr/fiche-jedi-yoda"
        Then I should see "Le Côté lumineux de la force"

    Scenario: I can create two Business entity page patterns differentiated by queries and access to their related Business Entity pages
        Given I open the hamburger menu
        Then I should see "Représentation métier"
        When I follow "Représentation métier"
        And I close the hamburger menu
        Then I should see "Ajouter une représentation métier"
        When I follow the tab "Jedi"
        And I should see "Ajouter une représentation métier"
        And I follow "Ajouter une représentation métier"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi Dark - {{item.name}}"
        And I fill in "URL" with "fiche-jedi-dark-{{item.slug}}"
        And I fill in "victoire_business_template_type[query]" with "WHERE item.side='dark'"
        And I follow "Créer"
        Then I should see "La représentation métier a bien été créée"
        And I wait 5 seconds
        And I should be on "/fr/victoire-dcms/business-template/show/5"
        Then I switch to "layout" mode
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I fill in "Côté de la force" with "Static Widget - Fiche Jedi Dark"
        And I submit the widget
        Then I should see "Victoire !"

        When I open the hamburger menu
        And I follow "Représentation métier"
        And I close the hamburger menu
        Then I should see "Ajouter une représentation métier"
        When I follow the tab "Jedi"
        And I should see "Ajouter une représentation métier"
        And I follow "Ajouter une représentation métier"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi Bright - {{item.name}}"
        And I fill in "URL" with "fiche-jedi-bright-{{item.slug}}"
        And I fill in "victoire_business_template_type[query]" with "WHERE item.side='bright'"
        And I follow "Créer"
        Then I should see "La représentation métier a bien été créée"
        And I wait 5 seconds
        And I should be on "/fr/victoire-dcms/business-template/show/6"
        Then I switch to "layout" mode
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I fill in "Côté de la force" with "Static Widget - Fiche Jedi Bright"
        And I submit the widget
        Then I should see "Victoire !"

        Given I am on "/fr/fiche-jedi-dark-anakin"
        Then I should see "Static Widget - Fiche Jedi Dark"
        Given I am on "/fr/fiche-jedi-bright-anakin"
        Then I should see "404 not found"

        Given I am on "/fr/fiche-jedi-bright-yoda"
        Then I should see "Static Widget - Fiche Jedi Bright"
        Given I am on "/fr/fiche-jedi-dark-yoda"
        Then I should see "404 not found"

    Scenario: I can override a pattern to add some specific content
        Given I open the hamburger menu
        Then I should see "Représentation métier"
        When I follow "Représentation métier"
        And I close the hamburger menu
        Then I should see "Ajouter une représentation métier"
        When I follow the tab "Jedi"
        And I should see "Ajouter une représentation métier"
        And I follow "Ajouter une représentation métier"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi - {{item.name}}"
        And I fill in "URL" with "fiche-jedi-{{item.slug}}"
        And I follow "Créer"
        Then I should see "La représentation métier a bien été créée"
        Given I am on "/fr/fiche-jedi-yoda"
        And I switch to "layout" mode
        When I select "Force" from the "1" select of "content" slot
        Then I should see "Créer"
        When I fill in "Côté de la force" with "Nouveau"
        And I submit the widget
        Then I should see "Le Côté Nouveau de la force"
        Given I am on "/fr/victoire-dcms/business-template/show/5"
        Then I should not see "Le Côté Nouveau de la force"

    Scenario: I add a BusinessEntity and check if its representation is accessible
        Given I open the hamburger menu
        Then I should see "Représentation métier"
        When I follow "Représentation métier"
        And I close the hamburger menu
        Then I should see "Ajouter une représentation métier"
        When I follow the tab "Jedi"
        And I should see "Ajouter une représentation métier"
        And I follow "Ajouter une représentation métier"
        Then I should see "Créer une représentation métier"
        When I fill in "Nom" with "Fiche Jedi - {{item.name}}"
        And I fill in "URL" with "fiche-jedi-{{item.slug}}"
        And I follow "Créer"
        Then I should see "La représentation métier a bien été créée"
        And I should be on "/fr/victoire-dcms/business-template/show/5"
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
        Then I should see "Le Côté obscure de la force"



