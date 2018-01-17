@mink:selenium2 @alice(Page) @reset-schema
Feature: I can edit multiple blogs in multiples locales

  Background:
    Given I maximize the window
    And I am on "/fr/"
    And I should see "Blog"

  @alice(Blogi18n)
  Scenario: I have one blog and one locale
    When I follow "Blog"
    Then I should see "Paramètres"
    When I follow the tab "Paramètres"
    Then I should see "FR"
    Then the "blog_settings_translations_fr_name" field should contain "blog"
    Then I should see "EN"
    When I follow the tab "EN"
    Then the "blog_settings_translations_en_name" field should contain "The Jedi Network"

  @alice(BlogWithLocalesi18n)
  Scenario: I have one blog and multiple locales
    When I follow "Blog"
    Then I should see "Choisissez la langue du blog"
    And I should see "Créer un article maintenant"
    When I follow "Créer un article maintenant"
    Then I should see "Créer un nouvel article"
    When I fill in "article_translations_fr_name" with "titre article"
    When I fill in "article_translations_fr_description" with "description fr"
    Then I should see "EN"
    When I follow the tab "EN"
    When I fill in "article_translations_en_name" with "title article"
    When I fill in "article_translations_en_description" with "description en"
    And I follow "Créer"
    Then I should be on "/fr/blog-fr/article-fr-article-titre-article"
    When I am on "/fr/"
    And I should see "Blog"
    When I follow "Blog"
    Then I should see "titre article"
    And I wait 2 seconds
    When I select "en" from "choose_blog_locale"
    Then I should see "title article"
    When I follow "title article"
    Then I should be on "en/blog-en/article-en-title-article"

  @alice(LocaleWithBlogsi18n)
  Scenario: I have one locale and multiple blogs
    When I follow "Blog"
    Then I should see "Choisissez votre blog"
    And I should see "Créer un article maintenant"
    When I follow "Créer un article maintenant"
    Then I should see "Créer un nouvel article"
    When I fill in "article_translations_fr_name" with "titre"
    When I fill in "article_translations_fr_description" with "description"
    And I follow "Créer"
    And I should be on "/fr/blog-1/article-1-titre"
    And I am on "/fr/"
    And I should see "Blog"
    When I follow "Blog"
    Then I should see "titre"
    And I wait 2 seconds
    When I select "blog 2" from "choose_blog_blog"
    And I should see "Créer un article maintenant"
    When I follow "Créer un article maintenant"
    Then I should see "Créer un nouvel article"
    When I fill in "article_translations_fr_name" with "titre2"
    When I fill in "article_translations_fr_description" with "description"
    And I follow "Créer"
    And I should be on "/fr/blog-2/article-2-titre2"
    And I am on "/fr/"
    And I should see "Blog"
    When I follow "Blog"
    And I wait 2 seconds
    When I select "blog 2" from "choose_blog_blog"
    Then I should see "titre2"

  @alice(BlogsWithLocalesi18n)
  Scenario: I have multiple blogs and multiple locales
    When I follow "Blog"
    Then I should see "Choisissez votre blog"
    And I should see "Créer un article maintenant"
    When I follow "Créer un article maintenant"
    Then I should see "Créer un nouvel article"
    When I fill in "article_translations_fr_name" with "titre1"
    When I fill in "article_translations_fr_description" with "description"
    Then I should see "EN"
    When I follow the tab "EN"
    When I fill in "article_translations_en_name" with "title1"
    When I fill in "article_translations_en_description" with "description en"
    And I follow "Créer"
    And I should be on "fr/blog-1-fr/article-1-fr-titre1"
    And I am on "/fr/"
    And I should see "Blog"
    When I follow "Blog"
    Then I should see "titre"
    And I wait 2 seconds
    When I select "fr" from "choose_blog_locale"
    And I wait 1 second
    Then I should see "blog 2 FR"
    When I select "blog 2 FR" from "choose_blog_blog"
    And I wait 1 second
    And I should see "Créer un article maintenant"
    When I follow "Créer un article maintenant"
    Then I should see "Créer un nouvel article"
    When I fill in "article_translations_fr_name" with "titre2"
    When I fill in "article_translations_fr_description" with "description"
    And I follow "Créer"
    And I should be on "fr/blog-2-fr/article-2-fr-titre2"
    And I am on "/fr/"
    And I should see "Blog"
    When I follow "Blog"
    And I wait 2 seconds
    Then I should see "titre1"
    When I select "en" from "choose_blog_locale"
    And I wait 1 second
    Then I should not see "blog 2 FR"
    And I should see "title1"
    When I select "fr" from "choose_blog_locale"
    And I wait 3 seconds
    When I select "blog 2 FR" from "choose_blog_blog"
    Then I should see "titre2"
