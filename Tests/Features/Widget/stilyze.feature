@mink:selenium2 @alice(Page) @alice(MediaFile) @reset-schema
Feature: Stylize a widget

    Background:
        Given I maximize the window

    Scenario: Change color for small device
        When I am on homepage
        And I switch to "layout" mode
        Then I should see "New content"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Force side"
        When I fill in "Force side" with "dark"
        And I submit the widget
        Then I should see "The dark side of the force"
        When I switch to "style" mode
        And I edit the "Force" widget
        Then I should see "Edit widget style"
        When I fill in "a_widget_style[containerBackgroundColor]" with "rgb(255, 0, 0)"
        And I open the widget style tab 'xs'
        And I fill in "a_widget_style[containerBackgroundColorXS]" with "rgb(0, 0, 255)"
        And I follow the link containing "UPDATE"
        And I wait 2 seconds
        And I reload the page
        And I resize the window to 1600x900
        Then I should see the css property "background-color" of "widget-1" with "rgb(255, 0, 0)"
        When I minimize the window
        Then I should see the css property "background-color" of "widget-1" with "rgb(0, 0, 255)"

    Scenario: Change image for small device
        When I am on homepage
        And I switch to "layout" mode
        Then I should see "New content"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Force side"
        When I fill in "Force side" with "dark"
        And I submit the widget
        Then I should see "The dark side of the force"
        When I switch to "style" mode
        And I edit the "Force" widget
        Then I should see "Edit widget style"
        When I fill in "a_widget_style[containerPadding]" with "100px"
        And I select "image" from "a_widget_style[containerBackgroundType]"
        Then I should find css element "input" with selector "name" and value "a_widget_style[containerBackgroundImage]"
        When I attach image with id "1" to victoire field "a_widget_style_containerBackgroundImage_widget"
        And I open the widget style tab 'xs'
        And I fill in "a_widget_style[containerPaddingXS]" with "100px"
        And I select "image" from "a_widget_style[containerBackgroundTypeXS]"
        Then I should find css element "input" with selector "name" and value "a_widget_style[containerBackgroundImageXS]"
        When I attach image with id "2" to victoire field "a_widget_style_containerBackgroundImageXS_widget"
        And I follow the link containing "UPDATE"
        And I wait 5 seconds
        And I reload the page
        And I maximize the window
        Then I should see background-image of "widget-1" with relative url "/uploads/55953304833d5.jpg"
        When I minimize the window
        Then I should see background-image of "widget-1" with relative url "/uploads/55dc8d8a4c9d3.jpg"

    Scenario: Change color of a Template and check children View css
        When I am on "/en/victoire-dcms/template/show/1"
        And I resize the window to 1600x900
        And I switch to "layout" mode
        Then I should see "New content"
        When I select "Force" from the "1" select of "main_content" slot
        Then I should see "Force side"
        When I fill in "Force side" with "dark"
        And I submit the widget
        Then I should see "The dark side of the force"
        When I switch to "style" mode
        And I edit the "Force" widget
        Then I should see "Edit widget style"
        When I select "Use a color" from "Background"
        And I fill in "a_widget_style[containerBackgroundColor]" with "rgb(255, 0, 0)"
        And I follow the link containing "UPDATE"
        And I wait 2 seconds
        And I reload the page
        And I should see the css property "background-color" of "widget-1" with "rgb(255, 0, 0)"
        When I am on the homepage
        Then I should see the css property "background-color" of "widget-1" with "rgb(255, 0, 0)"
        When I am on "/en/victoire-dcms/template/show/1"
        And I switch to "style" mode
        And I edit the "Force" widget
        Then I should see "Edit widget style"
        When I fill in "a_widget_style[containerBackgroundColor]" with "rgb(0, 0, 255)"
        And I follow the link containing "UPDATE"
        And I wait 2 seconds
        And I reload the page
        Then I should see the css property "background-color" of "widget-1" with "rgb(0, 0, 255)"
        When I am on the homepage
        Then I should see the css property "background-color" of "widget-1" with "rgb(0, 0, 255)"

    Scenario: Use quantum to change color depending on locale
        When I am on homepage
        And I switch to "layout" mode
        Then I should see "New content"
        When I select "Force" from the "1" select of "main_content" slot
        # Create EN quantum
        Then I should see "QUANTUM"
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
        Then I should see "The english side of the force"
        # Style EN
        When I switch to "style" mode
        And I edit the "Force" widget
        Then I should see "Edit widget style"
        When I fill in "a_widget_style[containerBackgroundColor]" with "rgb(255, 0, 0)"
        And I wait 5 seconds
        And I select quantum "FR"
        When I fill in "b_widget_style[containerBackgroundColor]" with "rgb(0, 0, 255)"
        And I follow the link containing "UPDATE"
        And I wait 2 seconds
        And I reload the page
        Then I should see the css property "background-color" of "widget-1" with "rgb(255, 0, 0)"
        When I am on "/fr/"
        Then I should see the css property "background-color" of "widget-2" with "rgb(0, 0, 255)"