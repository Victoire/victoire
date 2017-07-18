@mink:selenium2 @alice(Page)  @reset-schema
Feature: Create a blog

Background:
    Given I maximize the window
    And I am on homepage

    Scenario: I create a new blog
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "Aucun résultat"
        When I follow "Créer un nouveau blog"
        Then I should see "Nom"
        When I fill in "Nom" with "The Jedi network"
        And I follow "Créer"
        Then I should see "Page créée avec succès"
        And I switch to "layout" mode
        And I should see "Nouveau contenu"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Créer"
        When I fill in "Côté de la force" with "obscur"
        And I submit the widget

    @alice(Blog) @alice(BlogTemplate)
    Scenario: I create a new article
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi Network"
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

    @alice(Blog) @alice(BlogTemplate)
    Scenario: I create a new article with a slug
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi Network"
        And I should see "Créer un article maintenant"
        When I follow "Créer un article maintenant"
        Then I should see "Créer un nouvel article"
        And I should see "Si ce champ est vide, le slug sera auto-généré"
        When I fill in "article_translations_fr_name" with "I'm your father."
        When I fill in "article_translations_fr_description" with "This is a great description."
        When I fill in "article_translations_fr_slug" with "custom-slug"
        When I select "First blog template" from "Modèle à utiliser"
        And I follow "Créer"
        And I wait 5 seconds
        Then I should be on "/fr/the-jedi-network/custom-slug"

    @alice(Blog) @alice(Article) @alice(BlogTemplate)
    Scenario: I can view the Article list in the blog management window
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "Listes des articles"
        And I should see "I'm your father."
        And I should see "Anakin Skywalker"
