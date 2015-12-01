@mink:selenium2 @alice(Page) @reset-schema
Feature: Create a page

Background:
    Given I am logged in as "anakin@victoire.io"
     And I resize the window to 1024x720

  @smartStep
Scenario: I can create a new page
    Given I should see "Page"
    Given I select the option "Nouvelle page" in the dropdown "Page"
    And I should see "Créer"
    And I fill in "Nom" with "tatooine"
    Then I submit the widget
    And I should see "Page créée avec succès"
    And I should be on "/fr/tatooine"

  @alice(Template)
Scenario: I can change the name and the url of a given page
    Given I can create a new page
# TODO: Use EntityContext to avoid to recreate first scenario to test name change
#    Given the following page:
#        | name      | slug     | locale | template |
#        | tatooine  | tatooine | fr     | base     |
    And I am on "/fr/tatooine"
    And I select the option "Paramètres de la page" in the dropdown "Page"
    And I should see "Mettre à jour"
    Then I fill in "Nom" with "anoth"
    Then I fill in "victoire_page_settings_type_slug" with "anoth"
    And I submit the widget
    And I wait 5 seconds
    Then I should be on "/fr/anoth"
    And I should see "Page modifiée avec succès"
