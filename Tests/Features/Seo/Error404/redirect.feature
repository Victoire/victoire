@mink:selenium2 @alice(Page) @reset-schema @alice(Error404)
Feature: Redirect a 404 error

  Background:
    Given I maximize the window
    When I am on homepage
    Then I open the hamburger menu
    And I follow "menu-404-sub-item" from the "menu-redirection-main-item" dropdown menu
    And I wait 1 second

  Scenario: I can redirect an error with valid url
    When I click the "#collapse-icon-1" element
    And I wait 1 second
    And I select "url" in the "seo_bundle_redirection_link_linkType" select
    And I wait 1 second
    And I fill in "seo_bundle_redirection_link_url" with "http://Test"
    And I click the ".vic-btn.vic-text-white" element
    And I wait 1 second
    Then I should see 2 rows in the table
    And I should not see "http://victoire/fr/test1"

  Scenario: I cant redirect an error with unvalid url
    When I click the "#collapse-icon-1" element
    And I wait 1 second
    And I select "url" in the "seo_bundle_redirection_link_linkType" select
    And I wait 1 second
    And I fill in "seo_bundle_redirection_link_url" with "http://Test:"
    And I click the ".vic-btn.vic-text-white" element
    And I wait 1 second
    Then I should see "Cette valeur n'est pas une URL valide."
