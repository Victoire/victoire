@mink:selenium2 @alice(Page) @reset-schema
Feature: Create a seo

Background:
    Given I am logged in as "anakin@victoire.io"
    And I maximize the window

Scenario: I can add a seo
    Given I can create a new page
    And the title should be "tatooine"
    And I select the option "SEO" in the dropdown "Page"
    Then I should see "Paramètres SEO de la page "
    When I fill in "Meta Balise \"Title\"" with "Tatooine, planète désertique"
    And I fill in "Meta Balise \"Description\"" with "2 étoiles, 3 satellites"
    And I follow "Mettre à jour"
    Then I should see "Paramètres SEO modifiés avec succès"
    And the title should be "Tatooine, planète désertique"

Scenario: I can add use businessTemplate to manage vbp seo
    Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
    And I can create a new Business entity page pattern and create some content in the pattern
    And I am on "/fr/fiche-jedi-anakin"
    Then the title should be "Fiche Jedi - Anakin"
    Given I am on "/fr/victoire-dcms/business-template/show/5"
    And I select the option "SEO" in the dropdown "Page"
    Then I should see "Paramètres SEO de la page "
    When I fill in "Meta Balise \"Title\"" with "Maître de la Force - {{item.name}}"
    And I follow "Mettre à jour"
    Then I should see "Paramètres SEO modifiés avec succès"
    Given I am on "/fr/fiche-jedi-anakin"
    Then the title should be "Maître de la Force - Anakin"
