@mink:selenium2 @alice(Page) @reset-schema
Feature: Edit widgets quantums

    Background:
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        And I maximize the window

    Scenario: I can create widget and its quantums in a page
        Given I am on "/en/"
        When I switch to "layout" mode
        Then I should see "New content"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Create"
        # Create EN quantum
        And I should see "QUANTUM"
        When I fill in "_a_static_widget_force[side]" with "english"
        And I open the widget quantum collapse when static
        Then I should see "Quantum name"
        When I fill in "_a_static_widget_force[quantum]" with "EN"
        When I fill in "a_static_widget_force_criterias_0_operator" with "equal"
        And I select "en" from "a_static_widget_force_criterias_0_value"
        # Create FR quantum
        And I create a new quantum
        And I select quantum "De"
        Then I should see "QUANTUM"
        When I fill in "_b_static_widget_force[side]" with "français"
        And I open the widget quantum collapse when static
        Then I should see "Quantum name"
        When I fill in "_b_static_widget_force[quantum]" with "FR"
        And I fill in "b_static_widget_force_criterias_0_operator" with "equal"
        And I select "fr" from "b_static_widget_force_criterias_0_value"
        And I submit the widget
        # Check template in EN
        Given I am on "/en/victoire-dcms/template/show/1"
        Then I should not see "The english side of the force"
        # Check template in FR
        Given I am on "/fr/victoire-dcms/template/show/1"
        Then I should not see "Le côté français de la force"
        # Check page in EN
        Given I am on "/en/"
        Then I should see "The english side of the force"
        # Check page in FR
        Given I am on "/fr/"
        Then I should see "Le côté français de la force"

    Scenario: I can create widget and its quantums in a template
        Given I am on "/en/victoire-dcms/template/show/1"
        And I switch to "layout" mode
        And I should see "New content"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Create"
        # Create EN quantum
        And I should see "QUANTUM"
        When I fill in "_a_static_widget_force[side]" with "english"
        And I open the widget quantum collapse when static
        Then I should see "Quantum name"
        When I fill in "_a_static_widget_force[quantum]" with "EN"
        When I fill in "a_static_widget_force_criterias_0_operator" with "equal"
        And I select "en" from "a_static_widget_force_criterias_0_value"
        # Create FR quantum
        And I create a new quantum
        And I select quantum "De"
        Then I should see "QUANTUM"
        When I fill in "_b_static_widget_force[side]" with "français"
        And I open the widget quantum collapse when static
        Then I should see "Quantum name"
        When I fill in "_b_static_widget_force[quantum]" with "FR"
        And I fill in "b_static_widget_force_criterias_0_operator" with "equal"
        And I select "fr" from "b_static_widget_force_criterias_0_value"
        And I submit the widget
        # Check template in EN
        When I am on "/en/victoire-dcms/template/show/1"
        Then I should see "The english side of the force"
        # Check template in FR
        When I am on "/fr/victoire-dcms/template/show/1"
        # Template is rendered with default locale
        Then I should see "The français side of the force"
        # Check page in EN
        When I am on "/en/"
        Then I should see "The english side of the force"
        # Check page in FR
        When I am on "/fr/"
        Then I should see "Le côté français de la force"

    Scenario: I can edit quantums of a template's widget from a child page
        Given I am on "/en/victoire-dcms/template/show/1"
        And I switch to "layout" mode
        And I should see "New content"
        # Create a widget without quantum on template
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Create"
        And I should see "QUANTUM"
        When I fill in "a_static_widget_force_side" with "no quantum"
        And I submit the widget
        When I am on "/en/"
        Then I should see "The no quantum side of the force"
        When I switch to "edit" mode
        # Add quantums on children page
        And I edit the "Force" widget
        And I wait 3 seconds
        Then I should see "Warning!"
        And I should see "This content is owned by a parent template"
        And I should see "EDIT THE ORIGINAL CONTENT"
        When I follow "EDIT THE ORIGINAL CONTENT"
        And I wait 5 seconds
        Then I should not see "Warning!"
        And I should not see "This content is owned by a parent template"
        # Create EN quantum
        And I should see "QUANTUM"
        When I fill in "_a_static_widget_force[side]" with "english"
        And I open the widget quantum collapse when static
        Then I should see "Quantum name"
        When I fill in "_a_static_widget_force[quantum]" with "EN"
        When I fill in "a_static_widget_force_criterias_0_operator" with "equal"
        And I select "en" from "a_static_widget_force_criterias_0_value"
        # Create FR quantum
        And I create a new quantum
        And I select quantum "De"
        Then I should see "QUANTUM"
        When I fill in "_b_static_widget_force[side]" with "français"
        And I open the widget quantum collapse when static
        Then I should see "Quantum name"
        When I fill in "_b_static_widget_force[quantum]" with "FR"
        And I fill in "b_static_widget_force_criterias_0_operator" with "equal"
        And I select "fr" from "b_static_widget_force_criterias_0_value"
        And I submit the widget
        # Check template in EN
        When I am on "/en/victoire-dcms/template/show/1"
        Then I should see "The english side of the force"
        # Check template in FR
        When I am on "/fr/victoire-dcms/template/show/1"
        # Template is rendered with default locale
        Then I should see "The français side of the force"
        # Check page in EN
        When I am on "/en/"
        Then I should see "The english side of the force"
        # Check page in FR
        When I am on "/fr/"
        Then I should see "Le côté français de la force"