@mink:selenium2 @alice(Page) @reset-schema
Feature: Edit widgets quantums

    Background:
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        And I maximize the window

    Scenario: I can create widget and its quantums in a page
        Given I am on "/fr/"
        And I switch to "layout" mode
        And I should see "Nouveau contenu"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Créer"
        When I rename quantum "1" with "FR"
        And I wait 5 second
        And I select quantum "FR"
        And I fill in "a_static_widget_force_side" with "français"
        And I follow "Critères"
        And I fill in "a_static_widget_force_criterias_0_operator" with "equal"
        And I select "fr" from "a_static_widget_force_criterias_0_value"
        And I create a new quantum "EN"
        And I select quantum "EN"
        And I fill in "c_static_widget_force_side" with "english"
        And I follow "Critères"
        And I fill in "c_static_widget_force_criterias_0_operator" with "equal"
        And I select "en" from "c_static_widget_force_criterias_0_value"
        And I submit the widget
        Given I am on "/fr/victoire-dcms/template/show/1"
        Then I should not see "Le côté français de la force"
        Given I am on "/en/victoire-dcms/template/show/1"
        Then I should not see "The english Side of the force"
        Given I am on "/fr/"
        Then I should see "Le côté français de la force"
        Given I am on "/en/"
        Then I should see "The english Side of the force"

    Scenario: I can create widget and its quantums in a template
        Given I am on "/fr/victoire-dcms/template/show/1"
        And I switch to "layout" mode
        And I should see "Nouveau contenu"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Créer"
        When I rename quantum "1" with "FR"
        And I select quantum "FR"
        And I fill in "a_static_widget_force_side" with "français"
        And I follow "Critères"
        And I fill in "a_static_widget_force_criterias_0_operator" with "equal"
        And I select "fr" from "a_static_widget_force_criterias_0_value"
        And I create a new quantum "EN"
        And I select quantum "EN"
        And I fill in "c_static_widget_force_side" with "english"
        And I follow "Critères"
        And I fill in "c_static_widget_force_criterias_0_operator" with "equal"
        And I select "en" from "c_static_widget_force_criterias_0_value"
        And I submit the widget
        Given I am on "/fr/victoire-dcms/template/show/1"
        Then I should see "Le côté français de la force"
        Given I am on "/en/victoire-dcms/template/show/1"
        #Template is rendered with default locale
        Then I should see "Le côté english de la force"
        Given I am on "/fr/"
        Then I should see "Le côté français de la force"
        Given I am on "/en/"
        Then I should see "The english Side of the force"

    Scenario: I can edit quantums of a template's widget from a child page
        Given I am on "/fr/victoire-dcms/template/show/1"
        And I switch to "layout" mode
        And I should see "Nouveau contenu"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Créer"
        And I fill in "a_static_widget_force_side" with "sans quantum"
        And I submit the widget
        Given I am on "/fr/"
        Then I should see "Le côté sans quantum de la force"
        When I switch to "edit" mode
        And I edit the "Force" widget
        And I wait 3 seconds
        Then I should see "Attention !"
        And I should see "Ce contenu appartient à un modèle parent"
        And I follow "modifier le contenu original"
        And I wait 5 seconds
        Then I should not see "Attention !"
        And I should not see "Ce contenu appartient à un modèle parent"
        When I rename quantum "1" with "FR"
        And I select quantum "FR"
        And I fill in "a_static_widget_force_side" with "français"
        And I follow "Critères"
        And I fill in "a_static_widget_force_criterias_0_operator" with "equal"
        And I select "fr" from "a_static_widget_force_criterias_0_value"
        And I create a new quantum "EN"
        And I select quantum "EN"
        And I fill in "c_static_widget_force_side" with "english"
        And I follow "Critères"
        And I fill in "c_static_widget_force_criterias_0_operator" with "equal"
        And I select "en" from "c_static_widget_force_criterias_0_value"
        And I submit the widget
        Given I am on "/fr/victoire-dcms/template/show/1"
        Then I should see "Le côté français de la force"
        Given I am on "/en/victoire-dcms/template/show/1"
        #Template is rendered with default locale
        Then I should see "Le côté english de la force"
        Given I am on "/fr/"
        Then I should see "Le côté français de la force"
        Given I am on "/en/"
        Then I should see "The english Side of the force"