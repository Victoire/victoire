@mink:selenium2 @alice(Page) @alice(SpaceshipTemplates) @reset-schema
Feature: Business Domain Strategy

  Scenario: I create and edit a new spaceship in en and fr and access to their pages
    Given the following WidgetMaps:
      | id | action | position | parent | slot         | view              |
      | 1  | create |          |        | main_content | Spaceship template |
    And the following WidgetTexts:
      | mode           | widgetMap  | businessentityId | fields                          |
      | businessEntity | 1          | spaceship        | a:1:{s:7:"content";s:4:"name";} |
    And I maximize the window
    And I am on "/victoire-dcms/backend/spaceship/"
    Then I should see "Aucun résultat"
    When I follow "Nouveau vaisseau spatial"
    Then I should be on "/victoire-dcms/backend/spaceship/new"
    And I should see "Nouveau vaisseau spatial"
    When I follow the tab "Fr [Default]"
    And I fill in "space_ship[translations][fr][name]" with "Le Faucon Millénium"
    And I follow the tab "En"
    And I fill in "space_ship[translations][en][name]" with "Millenium Falcon"
    And I press "Créer"
    And I am on "fr/vaisseau-spatial-le-faucon-millenium"
    Then I should see "Le Faucon Millénium"
    When I am on "/en/spaceship-millenium-falcon"
    Then I should see "Millenium Falcon"
    When I am on "victoire-dcms/backend/spaceship/1/edit"
    And I follow the tab "Fr [Default]"
    And I fill in "space_ship[translations][fr][name]" with "Le X-Wing"
    And I fill in "space_ship[translations][fr][slug]" with "le-x-wing"
    And I follow the tab "En"
    And I fill in "space_ship[translations][en][name]" with "The X-Wing"
    And I fill in "space_ship[translations][en][slug]" with "the-x-wing"
    And I press "Mettre à jour"
    And I am on "fr/vaisseau-spatial-le-x-wing"
    Then I should see "Le X-Wing"
    When I am on "/en/spaceship-the-x-wing"
    Then I should see "The X-Wing"