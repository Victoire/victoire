@mink:selenium2 @alice(Page) @reset-schema @alice(Redirection)
Feature: Modify a redirection

  Background:
    Given I maximize the window
    When I am on homepage
    And I open the hamburger menu
    Then I follow "menu-redirection-sub-item" from the "menu-redirection-main-item" dropdown menu
    And I wait 1 second

  Scenario: I can modify a redirection with valid url
    Then I click the "#collapse-icon-1" element
    And I wait 1 second
    When I select "url" in the "seo_bundle_redirection_link_linkType" select from "redirection-1-form-container" form
    And I wait 1 second
    Then I fill in "seo_bundle_redirection_link_url" with "http://victoire/fr/new-output-1" from "redirection-1-form-container" form
    Then I click the "#redirection-form" element from "redirection-1-form-container" form
    And I wait 1 second
    And I should not see "http://victoire/fr/output-1"
    Then I should see "http://victoire/fr/new-output-1"

  Scenario: I cant modify a redirection with unvalid url
    Then I click the "#collapse-icon-1" element
    And I wait 1 second
    When I select "url" in the "seo_bundle_redirection_link_linkType" select from "redirection-1-form-container" form
    And I wait 1 second
    Then I fill in "seo_bundle_redirection_link_url" with "http://Test:" from "redirection-1-form-container" form
    Then I click the "#redirection-form" element from "redirection-1-form-container" form
    And I wait 1 second
    Then I should see "Cette valeur n'est pas une URL valide."
