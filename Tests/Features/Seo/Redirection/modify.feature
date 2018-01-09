@mink:selenium2 @reset-schema @alice(Page) @alice(Redirection)
Feature: Modify a redirection

  Background:
    Given I maximize the window
    When I am on "/fr"
    And I open the redirection menu
    And I wait 1 second

  Scenario: I can modify a redirection
    Given the list "redirections-list-container" should contain 3 elements
    When I click the "#collapse-icon-1" element
    And I select "url" linkType from "//div[@id='collapse-form-1']//select" field
    And I wait 1 second
    Then I fill in "//div[@id='collapse-form-1']//input" linkField with "http://localhost:8000/app_ci.php/fr/modified"
    And I click the "#redirection_1_form_submit" element
    And I wait 1 second
    Then I should see text matching "Redirection successfully modified!"
    And the list "redirections-list-container" should contain 3 elements
    And I wait 1 second
    # Text can be truncated by CSS, so we check only a part of the string
    And I should see text matching "https?:\/\/.+\/app_ci\.php\/fr\/modi"
