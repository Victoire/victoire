@alice(Page) @reset-schema
Feature: Authentication

    Scenario: Login as victoire admin
        Given I am on "/login"
        When I fill in "Email" with "anakin@victoire.io"
        And I fill in "Password" with "test"
        And I press "Embark"
        Then I should be on "/"

    Scenario: Login as a wrong user
        Given I am on "/login"
        When I fill in "Email" with "wrong@email.com"
        And I fill in "Password" with "something"
        And I press "Embark"
        Then I should see "Bad credentials"
