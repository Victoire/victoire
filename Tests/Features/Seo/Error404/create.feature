@mink:selenium2 @alice(Page) @reset-schema
Feature: Create new 404 error

  Background:
    Given I maximize the window
    When I am on "/fr/fake"
    Then I am on "/fr"
    Then I open the hamburger menu
    And I follow "menu-404-sub-item" from the "menu-redirection-main-item" dropdown menu
    And I wait 1 second

  Scenario: I can see the new 404 error
    Then I should see 1 rows in the table
    And The modal title should be "Liste des erreurs 404"
    And I should see "http://fr.victoire.io:8000/app_ci.php/fr/fake"