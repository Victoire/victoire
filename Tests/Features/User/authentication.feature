@mink:selenium2 @database @fixtures
Feature: Authentication
    Scenario: Login as victoire admin
	Given I am on "/login"
	When I fill in "email" with "anakin@victoire.io"
	And I fill in "password" with "test"
	And I press "Embarquer"
    Then I should see "Victoire"