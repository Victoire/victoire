@mink:selenium2 @alice(Page) @reset-schema @alice(Blog) @alice(BlogTemplate) @alice(Article)
Feature: Articles management

  Background:
    Given I maximize the window
    And I am on homepage

  Scenario: I can see publish and draft articles
    When I am on homepage
    And I should see "Blog"
    When I follow "Blog"
    Then I should see "I'm your father."
    And I should not see "Are you an angel?"
    And I should see "Drafts"
    When I follow "Drafts"
    Then I should see "Are you an angel?"
    And I should not see "I'm your father."

  Scenario: I can publish a draft
    When I am on homepage
    And I should see "Blog"
    When I follow "Blog"
    Then I should see "I'm your father."
    And I should not see "Are you an angel?"
    And I should see "Drafts"
    When I follow "Drafts"
    Then I should see "Are you an angel?"
    And I should not see "I'm your father."
    When I follow "Change parameters"
    And I wait 2 second
    Then I select "published" from "article_settings[status]"
    When I follow "UPDATE"
    Then I should be on "/en/the-jedi-network/are-you-an-angel"
    When I am on homepage
    And I should see "Blog"
    When I follow "Blog"
    Then I should see "I'm your father."
    And I should see "Are you an angel?"

  Scenario: I can draft published article
    When I am on homepage
    And I should see "Blog"
    When I follow "Blog"
    Then I should see "I'm your father."
    And I should not see "Are you an angel?"
    When I follow "Change parameters"
    And I wait 2 second
    Then I select "draft" from "article_settings[status]"
    When I follow "UPDATE"
    Then I should be on "/en/the-jedi-network/i-m-your-father"
    When I am on homepage
    And I should see "Blog"
    When I follow "Blog"
    Then I should not see "I'm your father."
    And I should not see "Are you an angel?"
    And I should see "Drafts"
    When I follow "Drafts"
    Then I should see "I'm your father."
    And I should see "Are you an angel?"
