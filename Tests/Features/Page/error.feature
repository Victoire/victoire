@mink:selenium2 @alice(Page) @alice(ErrorPage) @reset-schema
Feature: Error 404

    Background:
        Given I maximize the window
        And I am on homepage

    Scenario: I cannot access non-existent pages
        # I cannot access a non-existent page
        When I am on "/en/imaginary-page"
        Then the title should be "Error 404"
        # I cannot access a page for a non existent locale
        When I am on "/notalocale/"
        Then the title should be "Error 404"
        # I cannot access a non existent page for a non existent locale
        When I am on "/notalocale/imaginary-page"
        Then the title should be "Error 404"
        # I cannot access a page for a inconsistent locale
        When I am on "/notalocale:/"
        Then the title should be "Error 404"