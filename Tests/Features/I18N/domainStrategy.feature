@mink:selenium2 @alice(Page) @reset-schema
Feature: Get page content according to the domain

Background:
    Given I am on "http://fr.victoire.io:8000/app_domain.php/login"
    When I fill in "email" with "anakin@victoire.io"
    And I fill in "password" with "test"
    And I press "Embarquer"
    Then I should be on "http://fr.victoire.io:8000/app_domain.php/"
    And I resize the window to 1024x720

Scenario: I can create a page in two languages
    Given I should see "Page"
    Given I select the option "Nouvelle page" in the dropdown "Page"
    And I should see "Créer"
    And I fill in "Nom" with "Page francaise"
    Then I submit the modal
    And I should see "Page créée avec succès"
    And I should be on "http://fr.victoire.io:8000/app_domain.php/page-francaise"
    Given I select the option "Traduire" in the dropdown "Page"
    Then I should see "Nouvelle traduction de la page"
    When I fill in "Nom" with "English page"
    And I select "Anglais" from "Langue"
    And I wait 2 seconds
    And I follow "Mettre à jour"
    And I wait 7 seconds
    Then I should be on "http://en.victoire.io:8000/app_domain.php/english-page"