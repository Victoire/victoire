@mink:selenium2 @alice(Page) @alice(Template) @reset-schema
Feature: Test widgetMap
# Ececuted tests:
#  On a simple page with a template
#    - add
#    - delete
#    - move
#    - overwrite
#    - overwrite + add on child
#    - overwrite + delete on child
#    - overwrite + move on child
#    - overwrite + add on template
#    - overwrite + delete on template
#    - overwrite + move on template

  Background:
    Given I maximize the window
    And I am on homepage

@reset-schema
Scenario: I move first a widget from template
  Given the following WidgetMaps:
    | id | action | position | parent |  slot        |   view  |
    | 1  | create |          |        | main_content |   base  |
    | 2  | create |  after   |   1    | main_content |   base  |
    | 3  | create |  before  |   2    | main_content |   base  |
  Given the following WidgetTexts:
    | content  | mode   | widgetMap |
    | Widget 1 | static |    1       |
    | Widget 2 | static |    2       |
    | Widget 3 | static |    3       |
  And I am on the homepage
  Then I should see "Widget 1"
  When I move the widgetMap "3" "before" the widgetMap "1"
  And I wait 2 seconds
  And I reload the page
  And "Widget 3" should precede "Widget 1"
  And "Widget 1" should precede "Widget 2"
  Then I am on "/fr/victoire-dcms/template/show/1"
  And "Widget 1" should precede "Widget 3"
  And "Widget 3" should precede "Widget 2"

@reset-schema
Scenario: I move up a widget from template
  Given the following WidgetMaps:
    | id | action | position | parent |  slot        |   view  |
    | 1  | create |          |        | main_content |   base  |
    | 2  | create |  after   |   1    | main_content |   base  |
    | 3  | create |  before  |   2    | main_content |   base  |
  Given the following WidgetTexts:
    | content  | mode   | widgetMap |
    | Widget 1 | static |    1       |
    | Widget 2 | static |    2       |
    | Widget 3 | static |    3       |
  And I am on the homepage
  Then I should see "Widget 1"
  When I move the widgetMap "2" "before" the widgetMap "3"
  And I wait 2 seconds
  And I reload the page
  And "Widget 1" should precede "Widget 2"
  And "Widget 2" should precede "Widget 3"
  Then I am on "/fr/victoire-dcms/template/show/1"
  And "Widget 1" should precede "Widget 3"
  And "Widget 3" should precede "Widget 2"

@reset-schema
Scenario: I move down a widget from template
  Given the following WidgetMaps:
    | id | action | position | parent | slot         |   view  |
    | 1  | create |          |        | main_content |   base  |
    | 2  | create |  after   |   1    | main_content |   base  |
    | 3  | create |  before  |   2    | main_content |   base  |
  Given the following WidgetTexts:
    | content  | mode   | widgetMap |
    | Widget 1 | static |    1       |
    | Widget 2 | static |    2       |
    | Widget 3 | static |    3       |
  And I am on the homepage
  Then I should see "Widget 1"
  When I move the widgetMap "1" "after" the widgetMap "2"
  And I wait 2 seconds
  And I reload the page
  Then "Widget 3" should precede "Widget 2"
  Then "Widget 2" should precede "Widget 1"
  Then I am on "/fr/victoire-dcms/template/show/1"
  And "Widget 1" should precede "Widget 3"
  And "Widget 3" should precede "Widget 2"

@reset-schema
Scenario: I add widget in a position from template
  Given the following WidgetMaps:
    | id | action | position | parent | slot         |   view  |
    | 1  | create |          |        | main_content |   base  |
    | 2  | create |  after   |   1    | main_content |   base  |
    | 3  | create |  before  |   2    | main_content |   base  |
  Given the following WidgetTexts:
    | content  | mode   | widgetMap |
    | Widget 1 | static |    1       |
    | Widget 2 | static |    2       |
    | Widget 3 | static |    3       |
  And I am on the homepage
  Then I should see "Widget 1"
  When I switch to "layout" mode
  Then I should see "Nouveau contenu"
  When I select "Texte brut" from the "3" select of "main_content" slot
  Then I should see "Créer"
  When I fill in "Texte *" with "Widget 4"
  And I submit the widget
  And I reload the page
  And "Widget 1" should precede "Widget 3"
  And "Widget 3" should precede "Widget 4"
  And "Widget 4" should precede "Widget 2"
  Then I move the widgetMap "1" "after" the widgetMap "4"
  And I wait 2 seconds
  And I reload the page
  Then "Widget 3" should precede "Widget 4"
  Then "Widget 4" should precede "Widget 1"
  Then "Widget 1" should precede "Widget 2"
  Then I am on "/fr/victoire-dcms/template/show/1"
  And "Widget 1" should precede "Widget 3"
  And "Widget 3" should precede "Widget 2"

@reset-schema
Scenario: I delete widget from template
  Given the following WidgetMaps:
    | id | action | position | parent | slot         |   view  |
    | 1  | create |          |        | main_content |   base  |
    | 2  | create |  after   |   1    | main_content |   base  |
    | 3  | create |  before  |   2    | main_content |   base  |
  Given the following WidgetTexts:
    | content  | mode   | widgetMap |
    | Widget 1 | static |    1       |
    | Widget 2 | static |    2       |
    | Widget 3 | static |    3       |
  And I am on the homepage
  Then I should see "Widget 1"
  When I switch to "edit" mode
  And I edit the "Text" widget
  Then I should see "SUPPRIMER"
  Given I follow "SUPPRIMER"
  Then I should see "Cette action va définitivement supprimer ce contenu. Cette action est irréversible."
  And I should see "Êtes-vous sûr ?"
  Given I press "J'ai bien compris, je confirme la suppression"
  And I reload the page
  And "Widget 3" should precede "Widget 2"
  Then I am on "/fr/victoire-dcms/template/show/1"
  And "Widget 1" should precede "Widget 3"
  And "Widget 3" should precede "Widget 2"

@reset-schema
  Scenario: I overwrite a widget from template
  Given the following WidgetMaps:
    | id | action | position | parent | slot         |   view  |
    | 1  | create |          |        | main_content |   base  |
    | 2  | create |  after   |   1    | main_content |   base  |
    | 3  | create |  before  |   2    | main_content |   base  |
  Given the following WidgetTexts:
    | content  | mode   | widgetMap |
    | Widget 1 | static |    1       |
    | Widget 2 | static |    2       |
    | Widget 3 | static |    3       |
  And I am on the homepage
  Then I should see "Widget 1"
  When I switch to "edit" mode
  And I edit the "Text" widget
  Then I should see "Mettre à jour"
  When I fill in "Texte *" with "Widget 1 overwrite"
  And I submit the widget
  And I should see "Widget 1 overwrite"
  And "Widget 1 overwrite" should precede "Widget 3"
  And "Widget 3" should precede "Widget 2"
  Then I am on "/fr/victoire-dcms/template/show/1"
  And "Widget 1" should precede "Widget 3"
  And "Widget 3" should precede "Widget 2"

@reset-schema
Scenario: I move an overwrite widget from template
  Given the following WidgetMaps:
    | id | action | position | parent | slot         |   view  |
    | 1  | create |          |        | main_content |   base  |
    | 2  | create |  after   |   1    | main_content |   base  |
    | 3  | create |  before  |   2    | main_content |   base  |
  Given the following WidgetTexts:
    | content  | mode   | widgetMap |
    | Widget 1 | static |    1       |
    | Widget 2 | static |    2       |
    | Widget 3 | static |    3       |
    | Widget 3 overwrite | static |    4       |
  And I am on the homepage
  Then I should see "Widget 1"
  When I move the widgetMap "1" "after" the widgetMap "2"
  And I wait 2 seconds
  And I reload the page
  Then "Widget 2" should precede "Widget 1"
  Then I am on "/fr/victoire-dcms/template/show/1"
  And "Widget 1" should precede "Widget 3"
  And "Widget 3" should precede "Widget 2"

@reset-schema
Scenario: I add a widget after an overwrite widget from template
  Given the following WidgetMaps:
    | id | action    | position | parent | slot         |   view  | replaced |
    | 1  | create    |          |        | main_content |   base  |          |
    | 2  | create    |  after   |   1    | main_content |   base  |          |
    | 3  | create    |  before  |   2    | main_content |   base  |          |
    | 4  | overwrite |  before  |    2   | main_content |   home  |    3     |
  Given the following WidgetTexts:
    | content            | mode   | widgetMap |
    | Widget 1           | static |    1       |
    | Widget 2           | static |    2       |
    | Widget 3           | static |    3       |
    | Widget 3 overwrite | static |    4       |
  And I am on the homepage
  Then "Widget 1" should precede "Widget 3 overwrite"
  Then "Widget 3 overwrite" should precede "Widget 2"
  When I switch to "layout" mode
  Then I should see "Nouveau contenu"
  When I select "Texte brut" from the "3" select of "main_content" slot
  Then I should see "Créer"
  When I fill in "Texte *" with "Widget 4"
  And I submit the widget
  Then "Widget 1" should precede "Widget 3 overwrite"
  Then "Widget 3 overwrite" should precede "Widget 4"
  Then "Widget 4" should precede "Widget 2"
  Then I am on "/fr/victoire-dcms/template/show/1"
  And "Widget 1" should precede "Widget 3"
  And "Widget 3" should precede "Widget 2"

@reset-schema
Scenario: I delete an overwrite widget from template
  Given the following WidgetMaps:
    | id | action    | position | parent | slot         |   view  | replaced |
    | 1  | create    |          |        | main_content |   base  |          |
    | 2  | create    |  after   |   1    | main_content |   base  |          |
    | 3  | create    |  before  |   2    | main_content |   base  |          |
    | 4  | overwrite |  before  |    2   | main_content |   home  |    3     |
  Given the following WidgetTexts:
    | content            | mode   | widgetMap |
    | Widget 1           | static |    1       |
    | Widget 2           | static |    2       |
    | Widget 3           | static |    3       |
    | Widget 3 overwrite | static |    4       |
  And I am on the homepage
  When I switch to "edit" mode
  And I press the "Widget 3 overwrite" content
  Then I should see "SUPPRIMER"
  Given I follow "SUPPRIMER"
  Then I should see "Cette action va définitivement supprimer ce contenu. Cette action est irréversible."
  And I should see "Êtes-vous sûr ?"
  Given I press "J'ai bien compris, je confirme la suppression"
  And I reload the page
  Then I should see "Widget 1"
  Then I should see "Widget 2"
  Then I should see "Widget 3"
  Then I should not see "Widget 3 overwrite"
  Then I am on "/fr/victoire-dcms/template/show/1"
  And "Widget 1" should precede "Widget 3"
  And "Widget 3" should precede "Widget 2"

@reset-schema
Scenario: I move an overwrite widget on template
  Given the following WidgetMaps:
    | id | action    | position | parent | slot         |   view  | replaced |
    | 1  | create    |          |        | main_content |   base  |          |
    | 2  | create    |  after   |   1    | main_content |   base  |          |
    | 3  | create    |  before  |   2    | main_content |   base  |          |
    | 4  | overwrite |  before  |    2   | main_content |   home  |    3     |
  Given the following WidgetTexts:
    | content            | mode   | widgetMap |
    | Widget 1           | static |    1       |
    | Widget 2           | static |    2       |
    | Widget 3           | static |    3       |
    | Widget 3 overwrite | static |    4       |
  Then I am on "/fr/victoire-dcms/template/show/1"
  When I switch to "edit" mode
  When I move the widgetMap "3" "after" the widgetMap "2"
  And I wait 2 seconds
  And I reload the page
  And "Widget 1" should precede "Widget 2"
  And "Widget 2" should precede "Widget 3"
  And I am on the homepage
  Then "Widget 1" should precede "Widget 3 overwrite"
  Then "Widget 3 overwrite" should precede "Widget 2"

@reset-schema
Scenario: I add a widget after an overwrite widget on template
  Given the following WidgetMaps:
    | id | action    | position | parent | slot         |   view  | replaced |
    | 1  | create    |          |        | main_content |   base  |          |
    | 2  | create    |  after   |   1    | main_content |   base  |          |
    | 3  | create    |  before  |   2    | main_content |   base  |          |
    | 4  | overwrite |  before  |    2   | main_content |   home  |    3     |
  Given the following WidgetTexts:
    | content            | mode   | widgetMap |
    | Widget 1           | static |    1       |
    | Widget 2           | static |    2       |
    | Widget 3           | static |    3       |
    | Widget 3 overwrite | static |    4       |
  Then I am on "/fr/victoire-dcms/template/show/1"
  When I switch to "layout" mode
  Then I should see "Nouveau contenu"
  When I select "Texte brut" from the "3" select of "main_content" slot
  Then I should see "Créer"
  When I fill in "Texte *" with "Widget 4"
  And I submit the widget
  And I reload the page
  And "Widget 1" should precede "Widget 3"
  And "Widget 3" should precede "Widget 4"
  And "Widget 4" should precede "Widget 2"
  When I am on the homepage
  And "Widget 1" should precede "Widget 3 overwrite"
  And "Widget 3 overwrite" should precede "Widget 4"
  And "Widget 4" should precede "Widget 2"

@reset-schema
Scenario: I delete an overwrite widget on template
  Given the following WidgetMaps:
    | id | action    | position | parent | slot         |   view  | replaced |
    | 1  | create    |          |        | main_content |   base  |          |
    | 2  | create    |  after   |   1    | main_content |   base  |          |
    | 3  | create    |  before  |   2    | main_content |   base  |          |
    | 4  | overwrite |  before  |   2    | main_content |   home  |    3     |
  Given the following WidgetTexts:
    | content            | mode   | widgetMap |
    | Widget 1           | static |    1       |
    | Widget 2           | static |    2       |
    | Widget 3           | static |    3       |
    | Widget 3 overwrite | static |    4       |
  Then I am on "/fr/victoire-dcms/template/show/1"
  When I switch to "edit" mode
  And I press the "Widget 3" content
  Then I should see "SUPPRIMER"
  Given I follow "SUPPRIMER"
  Then I should see "Cette action va définitivement supprimer ce contenu. Cette action est irréversible."
  And I should see "Êtes-vous sûr ?"
  Given I press "J'ai bien compris, je confirme la suppression"
  And I reload the page
  Then I should see "Widget 1"
  Then I should see "Widget 2"
  Then I should not see "Widget 3"
  When I am on the homepage
  Then I should see "Widget 1"
  Then I should see "Widget 2"
  Then I should see "Widget 3 overwrite"

  @reset-schema
  Scenario: I delete a widget from template which has children WidgetMap in inherited page
    Given the following WidgetMaps:
      | id | action | position | parent | slot         | view | replaced |
      | 1  | create |          |        | main_content | base |          |
      | 2  | create | after    | 1      | main_content | base |          |
      | 3  | create | after    | 2      | main_content | home |          |
      | 4  | create | after    | 3      | main_content | home |          |
    Given the following WidgetTexts:
      | content  | mode   | widgetMap |
      | Widget 1 | static | 1         |
      | Widget 2 | static | 2         |
      | Widget 3 | static | 3         |
      | Widget 4 | static | 4         |
    When I am on the homepage
    Then I should see "Widget 1"
    And I should see "Widget 2"
    And I should see "Widget 3"
    And I should see "Widget 4"
    And I am on "/fr/victoire-dcms/template/show/1"
    Then I should see "Widget 1"
    And I should see "Widget 2"
    And I should not see "Widget 3"
    And I should not see "Widget 4"
    When I switch to "edit" mode
    And I press the "Widget 2" content
    Then I should see "Supprimer"
    When I follow "Supprimer"
    Then I should see "Cette action va définitivement supprimer ce contenu. Cette action est irréversible."
    And I should see "Êtes-vous sûr ?"
    When I press "J'ai bien compris, je confirme la suppression"
    And I reload the page
    Then I should see "Widget 1"
    And I should not see "Widget 2"
    And I should not see "Widget 3"
    And I should not see "Widget 4"
    When I am on the homepage
    Then I should see "Widget 1"
    And I should not see "Widget 2"
    And I should see "Widget 3"
    And I should see "Widget 4"