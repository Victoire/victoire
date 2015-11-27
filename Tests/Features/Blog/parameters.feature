@mink:selenium2 @alice(Page) @reset-schema @alice(Blog) @alice(Article)
Feature: Edit blog parameters

Background:
    Given I am logged in as "anakin@victoire.io"
    And I resize the window to 1024x720

    Scenario: I change the blog's name
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi network"
        And I follow "Paramètres"
        And I should see "Nom"
        When I fill in "Nom" with "The NEW Jedi network"
        And I press "Modifier"
        Then I should see "Victoire"
        Given I open the hamburger menu

    Scenario: I change the blog's url
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi network"
        And I follow "Paramètres"
        Then I should see "URL"
        #TODO And I fill in "URL" with "the-new-jedi-network"
        #TODO And I press "Modifier"
        #TODO Then I should see "Victoire"

    Scenario: I change the blog's status to draft
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi network"
        And I follow "Paramètres"
        Then I should see "Statut de publication"
        And I select "Brouillon" from "Statut de publication"
        And I press "Modifier"
        Then I should see "Victoire"
        And I wait 5 seconds
        And I should be on "/fr/the-jedi-network/"
        Given I open the hamburger menu

    Scenario: I change the blog's parent page
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi network"
        And I follow "Paramètres"
        Then I should see "Page parente"
        And I select "Test" from "Page parente"
        And I press "Modifier"
        Then I should see "Victoire"
        Then I go to "/fr/test/the-jedi-network/"
        Given I open the hamburger menu

    Scenario: I change the blog's model page
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi network"
        And I follow "Paramètres"
        Then I should see "Modèle"
        And I select "Two columns" from "Modèle"
        And I press "Modifier"
        Then I should see "Victoire"
        Then I go to "/fr/the-jedi-network/"
        Given I open the hamburger menu

    Scenario: I change the blog's language
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi network"
        And I follow "Paramètres"
        Then I should see "Langue"
        And I select "Anglais" from "Langue"
        And I press "Modifier"
        Then I should see "Victoire"
        Then I go to "/fr/the-jedi-network/"
        Given I open the hamburger menu

    Scenario: I edit the status of an article to draft in The Jedi Network
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi network"
        And I should see "I'm your father."
        When I follow "Modifier les paramètres"
        Then I should see "Statut de publication"
        And I select "Brouillon" from "Statut de publication"
        And I follow "Mettre à jour"
        Then I should see "Victoire"
        And I wait 5 seconds
        And I should be on "/fr/the-jedi-network/i-m-your-father"
        #TODO Given I open the hamburger menu - the page is not found

    Scenario: I edit the status of an article to published AT in The Jedi Network
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi network"
        And I should see "I'm your father."
        When I follow "Modifier les paramètres"
        Then I should see "Statut de publication"
        And I select "Publier à partir du" from "Statut de publication"
        And I wait 5 seconds
        #TODO Then I fill in "Date de publication" with "11/11/2045"
        #TODO And I follow "Mettre à jour"
        #TODO Then I should see "Victoire"
        #TODO And I wait 5 seconds
        #TODO And I should be on "/fr/the-jedi-network/i-m-your-father"
        #TODO Given I open the hamburger menu

    Scenario: I edit the name of an article in The Jedi Network
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi network"
        And I should see "I'm your father."
        When I follow "Modifier les paramètres"
        Then I fill in "I'm your father." with "My name is Anakin"
        Then I should see "Victoire"
        And I wait 5 seconds
        And I should be on "/fr/the-jedi-network/i-m-your-father"
        And I select the option "Paramètres de la page" in the dropdown "Page"
        And I should see "My name is Anakin"

    Scenario: I add some categories to the blog
        Given I open the hamburger menu
        Then I should see "Blog"
        When I follow "Blog"
        Then I should see "The Jedi network"
        And I follow "Liste des catégories"
        And I follow "Ajouter une catégorie principale"
        Then I should see "Catégorie"
        When I fill in "Catégorie" with "Naboo"
        #TODO And I press "Modifier" - impossible to specify the button, links to the first it finds, which is on the parameters tab
        #TODO Then I should see "Victoire"

