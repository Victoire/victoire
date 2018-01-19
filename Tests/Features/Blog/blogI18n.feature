@mink:selenium2 @reset-schema @alice(Page)
Feature: I can edit multiple blogs in multiples locales

  Background:
    Given I maximize the window
    And I am on homepage
    And I should see "Blog"

  @alice(Blogi18n)
  Scenario: I have one blog and one locale
    When I follow "Blog"
    Then I should see "Settings"
    When I follow the tab "Settings"
    Then I should see "FR"
    And the "blog_settings_translations_fr_name" field should contain "blog"
    And I should see "EN"
    When I follow the tab "EN"
    Then the "blog_settings_translations_en_name" field should contain "The Jedi Network"

  @alice(BlogWithLocalesi18n)
  Scenario: I have one blog and multiple locales
    # An English blog exists
    Given I am on "/en/blog-en"
    Then the title should be "blog EN"
    When I follow "Blog"
    Then I should see "Pick your blog's language"
    Then I should see "Create a post now"
    When I follow "Create a post now"
    Then I should see "Create a new post"
    When I fill in "article_translations_en_name" with "title article"
    And I fill in "article_translations_en_description" with "description en"
    And I should see "FR"
    And I follow the tab "FR *"
    And I fill in "article_translations_fr_name" with "titre article"
    And I fill in "article_translations_fr_description" with "description fr"
    And I follow "Create"
    And I wait 1 second
    Then the url should match "/en/blog-en/article-en-title-article"
    And the title should be "article EN title article"
    # Check that there is a French translation of this article
    When I go to "/fr/blog-fr/article-fr-titre-article"
    Then the title should be "article FR titre article"
    When I am on homepage
    And I should see "Blog"
    When I follow "Blog"
    Then I should see "title article"
    And I wait 2 seconds
    When I select "fr" from "choose_blog_locale"
    And I wait 2 seconds
    Then I should see "titre article"
    When I follow "titre article"
    Then the url should match "/fr/blog-fr/article-fr-titre-article"

  @alice(LocaleWithBlogsi18n)
  Scenario: I have one locale and multiple blogs
    Given I am on "/fr/"
    And I follow "Blog"
    Then I should see "Choisissez votre blog"
    And I should see "Créer un article maintenant"
    When I follow "Créer un article maintenant"
    Then I should see "Créer un nouvel article"
    When I fill in "article_translations_fr_name" with "titre"
    When I fill in "article_translations_fr_description" with "description"
    And I follow "Créer"
    And I wait 1 second
    Then the url should match "/fr/blog-1/article-1-titre"
    And I should see "Blog"
    When I follow "Blog"
    Then I should see "titre"
    And I wait 2 seconds
    When I select "blog 2" from "choose_blog_blog"
    Then I should see "Choisissez votre blog"
    And I should see "Créer un article maintenant"
    When I follow "Créer un article maintenant"
    Then I should see "Créer un nouvel article"
    When I fill in "article_translations_fr_name" with "titre2"
    When I fill in "article_translations_fr_description" with "description"
    And I follow "Créer"
    And I wait 1 second
    Then the url should match "/fr/blog-2/article-2-titre2"
    And I should see "Blog"
    When I follow "Blog"
    And I wait 2 seconds
    When I select "blog 2" from "choose_blog_blog"
    Then I should see "titre2"

  @alice(BlogsWithLocalesi18n)
  Scenario: I have multiple blogs and multiple locales
    When I follow "Blog"
    Then I should see "Pick your blog"
    And I should see "Create a post now"
    When I follow "Create a post now"
    Then I should see "Create a new post"
    When I fill in "article_translations_en_name" with "title1"
    And I fill in "article_translations_en_description" with "description en"
    And I should see "FR"
    And I follow the tab "FR *"
    When I fill in "article_translations_fr_name" with "titre1"
    When I fill in "article_translations_fr_description" with "description"
    And I follow "Create"
    And I wait 1 second
    And the url should match "/fr/blog-1-fr/article-1-fr-titre1"
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
    Then the url should match "/fr/blog-2-fr/article-2-fr-titre2"
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
