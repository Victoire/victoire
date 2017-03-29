@mink:selenium2 @alice(Page) @alice(User) @reset-schema
Feature: Manage jedis

    Background:
        Given I am on homepage

    Scenario: I can list jedis
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 27700         | anakin |
            | Yoda   | bright | 17700         | yoda   |
        Then I should see "Jedi"
        When I follow "Jedi"
        Then I should be on "/victoire-dcms/backend/jedi/"
        Then I should see "Jedis list"
        Then I should see the following table
            | Name   | Midichlorians | Side   |
            | Anakin | 27700         | dark   |
            | Yoda   | 17700         | bright |

    Scenario: I try to list jedis but there is no results
        Given I am on "/victoire-dcms/backend/jedi/"
        Then I should see "No result"

    Scenario: I create a new jedi
        Given I am on "/victoire-dcms/backend/jedi/"
        Then I should see "No result"
        When I follow "New jedi"
        Then I should be on "/victoire-dcms/backend/jedi/new"
        And I should see "New Jedi"
        When I fill in "Name" with "Anakin"
        And I fill in "MidiChlorians" with "27700"
        And I fill in "Slug" with "anakin"
        And I select "dark" from "Force side"
        And I press "Create"
        Then I should be on "/victoire-dcms/backend/jedi/"
        Then I should see "Jedis list"
        Then I should see the following table
            | Name   | Midichlorians | Side |
            | Anakin | 27700         | dark |

    Scenario: I delete a jedi
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 27700         | anakin |
            | Yoda   | bright | 17700         | yoda   |
        And I am on "/victoire-dcms/backend/jedi/"
        Then I should see the following table
            | Name   | Midichlorians | Side   |
            | Anakin | 27700         | dark   |
            | Yoda   | 17700         | bright |
        And I follow the 1st "Edit" link
        Then I should see "Jedi edit"
        When I press "Delete"
        Then I should be on "/victoire-dcms/backend/jedi/"
        Then I should see "Jedis list"
        Then I should see the following table
            | Name | Midichlorians | Side   |
            | Yoda | 17700         | bright |

    Scenario: I can rename the url of a jedi
        Given I maximize the window
        And the following Jedis:
            | name   | side | midiChlorians | slug   |
            | Anakin | dark | 27700         | anakin |
        And the following BusinessTemplate:
            | currentLocale | name                         | backendName  | slug                       | businessEntity | parent | template |
            | en            | Jedi profile - {{item.name}} | Jedi profile | jedi-profile-{{item.slug}} | Jedi           | home   | base     |
        And I wait 2 seconds
        And I am on "/en/jedi-profile-anakin"
        And I switch to "layout" mode
        And I should see "New content"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Force side"
        When I fill in "Force side" with "new"
        And I submit the widget
        And I wait 5 seconds
        Then I should see "The new side of the force"
        When I open the settings menu
        And I should see "UPDATE"
        When I fill in "page_settings_translations_en_slug" with "dark Vador"
        Then I should see an ".page_settings_translations_en_a2lix_translationsFields-en #page_settings_translations_en_slug-correct.v-color--green" element
        And I should not see an ".page_settings_translations_en_a2lix_translationsFields-en #page_settings_translations_en_slug-correct.v-color--red" element
        When I fill in "page_settings_translations_en_slug" with ""
        Then I should not see an ".page_settings_translations_en_a2lix_translationsFields-en #page_settings_translations_en_slug-correct.v-color--red" element
        And I should see an ".page_settings_translations_en_a2lix_translationsFields-en #page_settings_translations_en_slug-correct.v-color--green" element
        When I fill in "page_settings_translations_en_slug" with "dark-vador"
        Then I should not see an ".page_settings_translations_en_a2lix_translationsFields-en #page_settings_translations_en_slug-correct.v-color--red" element
        And I should see an ".page_settings_translations_en_a2lix_translationsFields-en #page_settings_translations_en_slug-correct.v-color--green" element
        When I submit the widget
        And I wait 5 seconds
        Then I should see "Successfully modified page"
        And I should be on "/en/dark-vador"
