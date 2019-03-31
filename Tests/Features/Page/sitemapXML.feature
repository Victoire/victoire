@javascript @alice(Page) @reset-schema
Feature: Generate sitemap.xml

  Background:
    Given I run the viewreference generation command
    Given I run the sitemap clear command
    And I am on the homepage

  Scenario: I go to the sitemap.xml page
    Given I am on "http://en.victoire.io:8000/app_domain.php/sitemap.xml"
    Then the response should contain "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">"
    And the response should contain "<loc>http://en.victoire.io:8000/app_domain.php/</loc>"
    And the response should contain "<loc>http://en.victoire.io:8000/app_domain.php/english-test</loc>"
    And the response should contain "<changefreq>monthly</changefreq>"
    And the response should contain "<priority>0.5</priority>"

    Given I am on "http://fr.victoire.io:8000/app_domain.php/sitemap.xml"
    Then the response should contain "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">"
    And the response should contain "<loc>http://fr.victoire.io:8000/app_domain.php/</loc>"
    And the response should contain "<loc>http://fr.victoire.io:8000/app_domain.php/test</loc>"
    And the response should contain "<changefreq>monthly</changefreq>"
    And the response should contain "<priority>0.5</priority>"

  Scenario: I create a page and go to the sitemap.xml page
    Given I should see "Page"
    When I select the option "Nouvelle page" in the dropdown "Page"
    Then I should see "Créer"
    And I fill in "Nom" with "Les derniers jedi"
    Then I submit the widget
    And I should see "Page créée avec succès"
    Given I am on "http://fr.victoire.io:8000/app_domain.php/sitemap.xml"
    Then the response should contain "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">"
    And the response should contain "<loc>http://fr.victoire.io:8000/app_domain.php/the-last-jedi</loc>"
    And the response should contain "<changefreq>monthly</changefreq>"
    And the response should contain "<priority>0.5</priority>"