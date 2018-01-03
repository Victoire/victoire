@mink:selenium2 @reset-schema @alice(Page) @alice(Redirection)
Feature: Create new redirection

  Background:
    Given I maximize the window
    When I am on "/fr"
    And I open the redirection menu
    And I wait 1 second

  Scenario: I can view the new error
    And The list "redirections-list-container" should contain 3 elements
    When I click the ".vic-btn-default" element
    And I wait 1 second
    And I fill in "seo_bundle_redirection[url]" with "http://test"
    And I select "url" from "seo_bundle_redirection[link][linkType]"
    And I wait 1 second
    Then I fill in "seo_bundle_redirection[link][url]" with "http://localhost:8000/app_ci.php/fr/"
    And I click the ".vic-btn-default" element
    # TODO : fix below lines, intercooler does not run correctly for this test
    # And I wait 1 second
    # Then I should see text matching "Redirection successfully created!"
    # And The list "redirections-list-container" should contain 4 elements
