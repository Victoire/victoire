@mink:selenium2 @alice(Page) @reset-schema
Feature: Display a page's content in a modal

  Background:
    Given I maximize the window
    And I am on homepage

  @alice(Redirection)
  Scenario: I can create a modal link to a page
    Given I open the hamburger menu
    Then I follow "Erreurs 404" form the "Redirection" menu
#    Then I should see "Redirections"
#    Then I follow "Redirections"
    And I follow "Erreurs 404"
