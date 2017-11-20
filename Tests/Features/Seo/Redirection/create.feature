@mink:selenium2 @alice(Page) @alice(Redirection) @reset-schema
Feature: Create new redirection

  Background:
    Given I maximize the window
    When I am on homepage
    Then I open the hamburger menu
    And I follow "menu-redirection-sub-item" from the "menu-redirection-main-item" dropdown menu
    And I wait 1 second

  Scenario: I can create new redirection with url output
    Given I click the "#new-redirection-button" element
    And I wait 1 second
    When I fill in "seo_bundle_redirection_url" with "http://victoire/fr/input-4"
    And I select "url" in the "seo_bundle_redirection_link_linkType" select
    And I wait 1 second
    And I fill in "seo_bundle_redirection_link_url" with "http://victoire/fr/output-4"
    Then I click the "#redirection-form" element
    And I wait 1 second
    Then I should see 4 rows in the table
    And I should see "http://victoire/fr/input-4"
    And I should see "http://victoire/fr/output-4"

  Scenario: I can create new redirection with viewReference output
    Given I click the "#new-redirection-button" element
    And I wait 1 second
    When I fill in "seo_bundle_redirection_url" with "http://victoire/fr/input-4"
    And I select "viewReference" in the "seo_bundle_redirection_link_linkType" select
    And I wait 1 second
    And I select "fr" in the "seo_bundle_redirection_link_locale" select
    And I wait 1 second
    And I select "ref_2_fr" in the "seo_bundle_redirection_link_viewReference" select
    Then I click the "#redirection-form" element
    And I wait 1 second
    Then I should see 4 rows in the table
    And I should see "http://victoire/fr/input-4"
    And I should see "/app_ci.php/fr/"