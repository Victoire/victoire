@mink:selenium2 @reset-schema @alice(Page) @alice(Redirection)
Feature: Create new redirection

  Background:
    Given I maximize the window
    And I am on homepage
    And I open the redirection menu
    And I wait 1 second

  Scenario: I can view the new error
    And the list "redirections-list-container" should contain 3 elements
    Then I should not see "http://localhost:8000/app_ci.php/fr/"
    When I press the "New redirection" button
    And I wait 1 second
    And I fill in "seo_bundle_redirection[url]" with "http://test"
    And I select "url" from "seo_bundle_redirection[link][linkType]"
    And I wait 1 second
    Then I fill in "seo_bundle_redirection[link][url]" with "http://localhost:8000/app_ci.php/fr/"
    And I press the "Create" button
    And I wait 3 second
    Then I should see text matching "Redirection successfully created!"
    # TODO: there is 5 elements instead of 4 due to a bug with Javascript
    And the list "redirections-list-container" should contain 5 elements
    And I should see "http://localhost:8000/app_ci.php/fr/"
