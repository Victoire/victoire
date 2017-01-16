@mink:selenium2 @alice(Page) @reset-schema @alice(Blog) @alice(BlogTemplate) @alice(Article)
Feature: Edit an article

Background:
    Given I maximize the window
    And I am on homepage

    Scenario: I can edit a blog article
        Given I am on "/fr/the-jedi-network/i-m-your-father"
        And I wait 3 seconds
        When I select the option "Paramètres de la page" in the dropdown "Page"
        And I should not see "Si ce champ est vide, le slug sera auto-généré"
        When I fill in "Slug" with "new-custom-slug"
        And I follow "Mettre à jour"
        And I wait 5 seconds
        Then I should be on "/fr/the-jedi-network/new-custom-slug"
