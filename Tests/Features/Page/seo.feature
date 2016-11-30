@mink:selenium2 @alice(Page) @reset-schema
Feature: Create a seo

Background:
    Given I maximize the window
    And I am on homepage

Scenario: I can add a seo
    Given the following PageSeo:
        | metaTitle | metaDescription |
        | Tatooine, planète désertique | 2 étoiles, 3 satellites |
    Given the following Page:
        | currentLocale |name     | slug     | parent  | template      |
        | fr            |tatooine | tatooine | home    | base          |

Scenario: I can add use businessTemplate to manage vbp seo
    Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
    Given the following BusinessTemplate:
        | currentLocale |name                       | backendName  | slug                    |  businessEntityName | parent  | template |
        | fr            |Fiche Jedi - {{item.name}} | Fiche Jedi  | fiche-jedi-{{item.slug}} |  jedi             | home    | base |
    And I am on "/fr/fiche-jedi-anakin"
    Then the title should be "Fiche Jedi - Anakin"
    Given I am on "/fr/victoire-dcms/business-template/show/4"
    And I select the option "SEO" in the dropdown "Page"
    Then I should see "Paramètres SEO de la page "
    When I fill in "Meta Balise \"Title\"" with "Maître de la Force - {{item.name}}"
    And I follow "Mettre à jour"
    Then I should see "Paramètres SEO modifiés avec succès"
    Given I am on "/fr/fiche-jedi-anakin"
    Then the title should be "Maître de la Force - Anakin"
