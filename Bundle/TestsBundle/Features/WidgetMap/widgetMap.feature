@mink:selenium2 @victoire
Feature: Create a widget at first position

Background:
    Given I am on "/"
        And I am logged in as "paul@appventus.com"

Scenario: Create a widget at first position
    Then I switch to edit mode "true"
    When I select "CKEditor" from the "1" select of "main_content" slot
    Then I should see "Créer"
    When I fill in wysiwyg with "premier test"
    And I wait 1 seconds
    And I submit the widget
    And I wait 1 seconds
    And I reload the page
    Then I should see "premier test"

    When I select "CKEditor" from the "2" select of "main_content" slot
    Then I should see "Créer"
    When I fill in wysiwyg with "second test"
    And I wait 1 seconds
    And I submit the widget
    And I wait 1 seconds
    And I reload the page
    Then I should see "second test"
    And "premier test" should precede "second test"

    When I select "CKEditor" from the "1" select of "main_content" slot
    Then I should see "Créer"
    When I fill in wysiwyg with "troisieme test"
    And I wait 1 seconds
    And I submit the widget
    And I wait 1 seconds
    And I reload the page
    Then I should see "troisieme test"
    And "troisieme test" should precede "second test"
    And "troisieme test" should precede "premier test"
    When I select "CKEditor" from the "3" select of "main_content" slot
    Then I should see "Créer"
    When I fill in wysiwyg with "quatrieme test"
    And I wait 1 seconds
    And I submit the widget
    And I wait 1 seconds
    And I reload the page
    Then I should see "quatrieme test"
    And "quatrieme test" should precede "second test"
    And "premier test" should precede "quatrieme test"
    And "troisieme test" should precede "quatrieme test"
