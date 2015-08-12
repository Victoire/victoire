@alice(Page) @reset-schema
Feature: Authentication
    Scenario: Login as victoire admin
        Given I am on "/login"
        When I fill in "email" with "anakin@victoire.io"
        And I fill in "password" with "test"
        And I press "Embarquer"
        Then I should be on "/fr/"
    Scenario: Login as a wrong user
        Given I am on "/login"
        When I fill in "email" with "wrong@email.com"
        And I fill in "password" with "something"
        And I press "Embarquer"
        Then I should see "Bad credentials"