@mink:selenium2 @alice(Page) @alice(Template) @reset-schema
Feature: Edit the global website configuration

    Background:
        Given I am on homepage
        When I open the additional menu drop
        Then I should see "Website configuration"
        When I follow "Website configuration"

    Scenario: I can set a meta title pattern
        Then the title should be "Homepage"
        Then I should see "META title pattern"
        When I fill in "META title pattern" with "%page.title% - Victoire"
        And I follow "UPDATE"
        And I wait 5 seconds
        Then the title should be "Homepage - Victoire"

    Scenario: I need to a have at least the %page.title% variable in meta title pattern
        Then the title should be "Homepage"
        Then I should see "META title pattern"
        When I fill in "META title pattern" with "%page.name% - Victoire"
        And I follow "UPDATE"
        Then I should see "You need at least %page.title%"
        When I fill in "META title pattern" with "%page.title% - Victoire"
        And I follow "UPDATE"
        And I wait 5 seconds
        Then the title should be "Homepage - Victoire"

    Scenario: I can set the main color and then the mobile browsers are painted with this color
        Then I should see "Main color"
        When I fill in "Main color" with "#9676a8"
        And I follow "UPDATE"
        And I wait 5 seconds
        Then the meta "theme-color" should be set to "#9676a8"
        Then the meta "msapplication-TileColor" should be set to "#9676a8"

    Scenario: I can add insert and eval a code to every page
        Then I should not see "This is the dark side"
        Then I should see "Code to insert in every page's HEAD section"
        When I fill in "Code to insert in every page's HEAD section" with "<script>document.write('This is the dark side');</script>"
        And I follow "UPDATE"
        And I wait 5 seconds
        Then I should see "This is the dark side"

    @alice(MediaFile)
    Scenario: I can set the logo of my website and the favicons are generated
        Given the response should contain "<link rel=\"icon\" type=\"image/x-icon\" href=\"/favicon.ico?version=1\" />"
        Then I should see "Icons"
        When I follow "Icons"
        When I attach image with id "1" to victoire field "global_config[logo]"
        And I follow "UPDATE"
        And I wait 15 seconds
        Then the response should not contain "<link rel=\"icon\" type=\"image/x-icon\" href=\"/favicon.ico?version=1\" />"
        #no more version=1 but a version based on the date (version=19700101000000) so it's a bit hard to guess it oO

    Scenario: I can add a json+ld definition to be semantically recognized by bots
        Then I should see "Semantical definition of the organization"
        When I fill in "Semantical definition of the organization" with:
"""
{
  "@context": "http://schema.org",
  "@type": "Organization",
  "url": "http://www.example.com",
  "name": "Unlimited Ball Bearings Corp.",
  "contactPoint": {
    "@type": "ContactPoint",
    "telephone": "+1-401-555-1212",
    "contactType": "Customer service"
  }
}
"""
        Given the response should not contain "<!-- Semantic markup (https://schema.org) -->"
        And I follow "UPDATE"
        And I wait 5 seconds
        Then I should see "The global configuration has been updated"
        And the response should contain "<!-- Semantic markup (https://schema.org) -->"
