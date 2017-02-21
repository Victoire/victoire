@mink:selenium2 @alice(Page) @reset-schema
Feature: Create a page

Background:
    Given I maximize the window
    And I am on homepage

  @smartStep
Scenario: I can create a new page
    When I follow the float action button
    Then I should see "Nouvelle page"
    And I follow "Nouvelle page"
    And I should see "Créer"
    And I fill in "Nom" with "tatooine"
    Then I submit the widget
    And I should see "Page créée avec succès"
    And I should be on "/fr/tatooine"

  @alice(Template)
Scenario: I can change the name and the url of a given page
      Given the following Page:
          | currentLocale |name     | slug     | parent  | template      |
          | fr            |tatooine | tatooine | home    | base          |
    And I am on "/fr/tatooine"
    And I open the settings menu
    And I should see "Mettre à jour"
    Then I fill in "Nom" with "anoth"
    Then I fill in "page_settings_translations_fr_slug" with "anoth"
    And I submit the widget
    And I wait 5 seconds
    Then I should be on "/fr/anoth"
    And I should see "Page modifiée avec succès"
