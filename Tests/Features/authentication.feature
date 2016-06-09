@alice(Page) @reset-schema
Feature: Authentication
    Scenario: Login as victoire admin
        Given I am on "/login"
        When I fill in "Adresse email" with "anakin@victoire.io"
        And I fill in "Mot de passe" with "test"
        And I press "Embarquer"
        Then I should be on "/"
    Scenario: Login as a wrong user
        Given I am on "/login"
        When I fill in "Adresse email" with "wrong@email.com"
        And I fill in "Mot de passe" with "something"
        And I press "Embarquer"
        Then I should see "Bad credentials"
