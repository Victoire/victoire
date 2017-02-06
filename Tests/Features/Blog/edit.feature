@reset-schema
@mink:selenium2
@alice(Page) @alice(Blog) @alice(BlogTemplate)
Feature: Edit an article

Background:
    Given I maximize the window
    And I am on homepage

    Scenario: I create a new article and update its slug
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi network"
        And I should see "Créer un article maintenant"
        When I follow "Créer un article maintenant"
        Then I should see "Créer un nouvel article"
        And I should see "Si ce champ est vide, le slug sera auto-généré"
        When I fill in "article_translations_fr_name" with "I'm your father."
        When I fill in "article_translations_fr_description" with "This is a great description."
        When I select "First blog template" from "Modèle à utiliser"
        And I follow "Créer"
        And I wait 5 seconds
        Then I should be on "/fr/the-jedi-network/i-m-your-father"
        And I wait 3 seconds
        When I select the option "Paramètres de la page" in the dropdown "Page"
        And I wait 3 seconds
        Then I should see "Paramètres de l'article I'm your father."
        And I should not see "Si ce champ est vide, le slug sera auto-généré"
        When I fill in "article_settings_translations_fr_slug" with "new-custom-slug"
        And I follow "Mettre à jour"
        And I wait 5 seconds
        Then I should be on "/fr/the-jedi-network/new-custom-slug"
