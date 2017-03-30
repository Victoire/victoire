@mink:selenium2 @alice(Page) @reset-schema
Feature: Create a seo

    Background:
        Given I maximize the window
        And I am on homepage

    Scenario: I can add a seo
        Given the following PageSeo:
            | metaTitle               | metaDescription       |
            | Tatooine, desert planet | 2 stars, 3 satellites |
        Given the following Page:
            | currentLocale | name     | slug     | parent | template |
            | en            | tatooine | tatooine | home   | base     |

    Scenario: I can add use businessTemplate to manage vbp seo
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        Given the following BusinessTemplate:
            | currentLocale | name                         | backendName  | slug                       | businessEntityId | parent | template |
            | en            | Jedi profile - {{item.name}} | Jedi profile | jedi-profile-{{item.slug}} | jedi             | home   | base     |
        And I am on "/en/jedi-profile-anakin"
        Then the title should be "Jedi profile - Anakin"
        Given I am on "/en/victoire-dcms/business-template/show/4"
        And I should see "SEO"
        Then I follow "SEO"
        Then I should see "SEO Settings"
        When I fill in "Meta Tag Title" with "Master of the Force - {{item.name}}"
        And I follow the link containing "UPDATE"
        Then I should see "SEO parameters successfully updated"
        Given I am on "/en/jedi-profile-anakin"
        Then the title should be "Master of the Force - Anakin"

    Scenario: I can manage sitemap priorities and indexation for each page translation
        When I am on "/en/"
        And I follow "SEO"
        Then I should see "SEO Settings"
        When I follow the tab "EN"
        And I select "Advanced" from the collapse menu
        And I check the 1st "Indexed URL" checkbox
        And I select "0.9" from "page_seo[translations][en][sitemapPriority]"
        When I follow the tab "FR"
        And I select "Advanced" from the collapse menu
        And I uncheck the 2nd "Indexed URL" checkbox
        And I follow the link containing "UPDATE"
        Then I should see "SEO parameters successfully updated"