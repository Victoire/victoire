@mink:selenium2 @reset-schema @alice(Page) @alice(Error404)
Feature: Redirect a 404 error

  Background:
    Given I maximize the window
    When I am on "/fr"
    And I open the 404 menu
    And I wait 1 second

  Scenario: I can redirect an error with valid website page
    Given The list "route-list" should contain 3 elements
    When I click the "#collapse-icon-1" element
    And I select "viewReference" from "seo_bundle_redirection[link][linkType]"
    And I wait 1 second
    And I select "fr" from "seo_bundle_redirection[link][locale]"
    And I wait 1 second
    And I select "ref_2_fr" from "seo_bundle_redirection[link][viewReference]"
    And I click the ".vic-btn-default" element
    And I wait 1 second
    Then I should see text matching "404 error successfully redirected!"
    And The list "route-list" should contain 2 elements

  Scenario: I can redirect an error with valid url
    Given The list "route-list" should contain 3 elements
    When I click the "#collapse-icon-1" element
    And I select "url" from "seo_bundle_redirection[link][linkType]"
    And I wait 1 second
    Then I fill in "seo_bundle_redirection[link][url]" with "http://localhost:8000/app_ci.php/fr/"
    And I click the ".vic-btn-default" element
    And I wait 1 second
    Then I should see text matching "404 error successfully redirected!"
    And The list "route-list" should contain 2 elements

  Scenario: I can't redirect an error with unvalid url
    Given The list "route-list" should contain 3 elements
    When I click the "#collapse-icon-1" element
    And I select "url" from "seo_bundle_redirection[link][linkType]"
    And I wait 1 second
    Then I fill in "seo_bundle_redirection[link][url]" with "unvalidUrl"
    And I click the ".vic-btn-default" element
    And I wait 1 second
    Then I should not see text matching "404 error successfully redirected!"
    And The list "route-list" should contain 3 elements
