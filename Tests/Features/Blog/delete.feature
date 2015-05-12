@mink:selenium2 @alice(Page) @reset-schema @alice(Blog) @alice(Article)
Feature: Delete a blog (and article)

Background:
    Given I am logged in as "anakin@victoire.io"
     And I maximize the window

    Scenario: I can delete a blog article
        Given I am on "/fr/the-jedi-network/i-m-your-father"
        When I select the option "Paramètres de l'article" in the dropdown "Blog"
        Then I should see "Supprimer"
        Given I follow "Supprimer"
        Then I should see "Cette action va supprimer définitivement cet article. Cette action est irréversible. Etes vous sûr ?"
        Given I press "J'ai bien compris, je confirme la suppression"
        And I wait 10 seconds
        And I should be on "/fr/the-jedi-network"

    Scenario: I can delete a blog
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi network"
        And I should see "Supprimer"
        Given I follow "Supprimer"
        Then I should see "Cette action va supprimer définitivement cette page. Cette action est irréversible. Etes vous sûr ?"
        Given I press "J'ai bien compris, je confirme la suppression"
        And I wait 10 seconds
        And I should be on "/fr/"
