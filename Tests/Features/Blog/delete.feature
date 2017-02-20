@mink:selenium2 @alice(Page) @reset-schema @alice(Blog) @alice(BlogTemplate) @alice(Article)
Feature: Delete a blog (and article)

Background:
    Given I maximize the window
    And I am on homepage

    Scenario: I can delete a blog article
        Given I am on "/fr/the-jedi-network/i-m-your-father"
        And I wait 3 seconds
        When I open the settings menu
        Then I should see "Supprimer"
        When I follow the link containing "Supprimer"
        Then I should see "Cette action va supprimer définitivement cette page. Cette action est irréversible. Êtes-vous sûr ?"
        Given I press "J'ai bien compris, je confirme la suppression"
        #TODO Then I should see "L'article a bien été supprimé"
        And I wait 10 seconds
        And I should be on "/fr/the-jedi-network"

    Scenario: I can delete a blog
        Then I should see "Blog"
        When I follow "Blog"
        And I wait 2 seconds
	    Then I should see "Gestion des blogs"
        And I should see "The Jedi Network"
        And I follow "Paramètres"
        And I should see "Supprimer"
        When I follow the link containing "Supprimer"
        Then I should see "Cette action va supprimer définitivement cette page. Cette action est irréversible. Êtes-vous sûr ?"
        Given I press "J'ai bien compris, je confirme la suppression"
        And I should be on "/fr/"
