@mink:selenium2 @alice(Page) @reset-schema
Feature: Create a page

Background:
    Given I maximize the window
    And I am on homepage

  @smartStep
Scenario: I can create a new page
    Given I should see "Page"
    Given I select the option "Nouvelle page" in the dropdown "Page"
    And I should see "Créer"
    And I fill in "Nom" with "tatooine"
    Then I submit the widget
    And I wait 2 seconds
    Then I should see "Page créée avec succès"
    And I should be on "/fr/tatooine"

  @alice(Template)
Scenario: I can change the name and the url of a given page
      Given the following Page:
          | currentLocale |name     | slug     | parent  | template      |
          | fr            |tatooine | tatooine | home    | base          |
    And I am on "/fr/tatooine"
    And I select the option "Paramètres de la page" in the dropdown "Page"
    And I should see "Mettre à jour"
    Then I fill in "Nom" with "anoth"
    Then I fill in "page_settings_translations_fr_slug" with "anoth"
    And I submit the widget
    And I wait 5 seconds
    Then I should be on "/fr/anoth"
    And I should see "Page modifiée avec succès"
