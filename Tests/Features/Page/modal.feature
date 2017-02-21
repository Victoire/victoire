@mink:selenium2 @alice(Page) @reset-schema
Feature: Display a page's content in a modal

  Background:
    Given I maximize the window
    And I am on homepage

  @debug
  Scenario: I can create a modal link to a page
    Given I switch to "layout" mode
    And I should see "Nouveau contenu"
    When I select "Bouton" from the "1" select of "main_content" slot
    Then I should see "Créer"
    When I fill in "Libellé" with "Render test in a modal"
    And I select "Page du site" from "Type de lien"
    Then I should see "Choisissez une page"
    And I should not see "Apparence de la modale"
    Given I select "└── Test" from "Page du site"
    And I select "Dans une modale/fenêtre" from "Ouverture du lien"
    Then I should see "Apparence de la modale"
    Given I select "Ma modale perso" from "Apparence de la modale"
    And I submit the widget
    And I should see "Render test in a modal"

    Given I am on "/fr/test"
    And I switch to "layout" mode
    Then I should see "Nouveau contenu"
    And I select "Force" from the "1" select of "main_content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "obscur"
    And I submit the widget
    Then I should see "Le côté obscur de la force"

    Given I am on homepage
    And I follow "Render test in a modal"
    Then I should see "My Custom modal"
    Then I should not see "Le côté obscur de la force"

    Given I am on homepage
    And I switch to "edit" mode
    And I edit the "Button" widget
    Then I should see "Apparence de la modale"
    When I select "Par défaut" from "Apparence de la modale"
    And I submit the widget

    Given I should see "Render test in a modal"
    When I switch to "readonly" mode
    And I follow "Render test in a modal"
    Then I should not see "My Custom modal"
    And I should see "Le côté obscur de la force"
