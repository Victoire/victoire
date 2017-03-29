@mink:selenium2 @alice(Page) @reset-schema @alice(Blog) @alice(BlogTemplate) @alice(Article)
Feature: Delete a blog (and article)

Background:
    Given I maximize the window
    And I am on homepage

    Scenario: I can delete a blog article
        Given I am on "/en/the-jedi-network/i-m-your-father"
        And I wait 3 seconds
        When I open the settings menu
        Then I should see "DELETE"
        When I follow the link containing "DELETE"
        Then I should see "This action will permanently remove the post. This action is irreversible. Are you sure?"
        Given I press "YES, I WANT TO DELETE IT!"
        Then I should see "The post has been removed"
        And I should be on "/en/the-jedi-network"

    Scenario: I can delete a blog
        Then I should see "Blog"
        When I follow "Blog"
	    Then I should see "Blog index"
        And I should see "The Jedi Network"
        And I follow "Settings"
        And I should see "DELETE"
        When I follow the link containing "DELETE"
        Then I should see "This action will permanently delete this page from the database. This action is irreversible."
        Given I press "YES, I WANT TO DELETE IT!"
        And I should be on "/en/"
