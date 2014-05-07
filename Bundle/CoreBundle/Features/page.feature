@javascript
Feature: Page
    Scenario: Create a page

        Given the following pages:
            | title | layout |
            | test  | 3cols  |

      Given I am on the "page" "test" "show" page
      When I select the option "Nouvelle page" in the dropdown "Page"
      Then I should see "Sauvegarder"

      When I fill in the following:
          | Title  | test2 |
          | Layout | 3cols |
      Then I should see "Sauvegarder"
      When I follow "Sauvegarder"
      Then I should be on the "page" "test2" "show" page

#TODO
#vérifier la migration d'une page en template
#vérifier le détachage d'une page
