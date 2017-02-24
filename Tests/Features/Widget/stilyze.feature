@mink:selenium2 @alice(Page) @alice(MediaFile) @reset-schema
Feature: Stylize a widget

  Background:
    Given I maximize the window

  Scenario: Change color for small device
    When I am on homepage
    And I switch to "layout" mode
    Then I should see "Nouveau contenu"
    When I select "Force" from the "1" select of "main_content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "obscur"
    And I submit the widget
    Then I should see "Le côté obscur de la force"
    When I switch to "style" mode
    And I edit the "Force" widget
    Then I should see "Style du widget"
    When I fill in "widget_style[containerBackgroundColor]" with "rgb(255, 0, 0)"
    And I open the widget style tab 'xs'
    And I fill in "widget_style[containerBackgroundColorXS]" with "rgb(0, 0, 255)"
    And I follow the link containing "Mettre à jour"
    And I wait 2 seconds
    And I reload the page
    And I resize the window to 1600x900
    Then I should see the css property "background-color" of "widget-1" with "rgb(255, 0, 0)"
    When I minimize the window
    Then I should see the css property "background-color" of "widget-1" with "rgb(0, 0, 255)"

  Scenario: Change image for small device
    When I am on homepage
    And I switch to "layout" mode
    Then I should see "Nouveau contenu"
    When I select "Force" from the "1" select of "main_content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "obscur"
    And I submit the widget
    Then I should see "Le côté obscur de la force"
    When I switch to "style" mode
    And I edit the "Force" widget
    Then I should see "Style du widget"
    When I fill in "widget_style[containerPadding]" with "100px"
    And I select "image" from "widget_style[containerBackgroundType]"
    Then I should find css element "input" with selector "name" and value "widget_style[containerBackgroundImage]"
    When I attach image with id "1" to victoire field "widget_style_containerBackgroundImage_widget"
    And I open the widget style tab 'xs'
    And I fill in "widget_style[containerPaddingXS]" with "100px"
    And I select "image" from "widget_style[containerBackgroundTypeXS]"
    Then I should find css element "input" with selector "name" and value "widget_style[containerBackgroundImageXS]"
    When I attach image with id "2" to victoire field "widget_style_containerBackgroundImageXS_widget"
    And I follow the link containing "Mettre à jour"
    And I wait 5 seconds
    And I reload the page
    And I maximize the window
    Then I should see background-image of "widget-1" with relative url "/uploads/55953304833d5.jpg"
    When I minimize the window
    Then I should see background-image of "widget-1" with relative url "/uploads/55dc8d8a4c9d3.jpg"

  Scenario: Change color of a Template and check children View css
    When I am on "/fr/victoire-dcms/template/show/1"
    And I resize the window to 1600x900
    And I switch to "layout" mode
    Then I should see "Nouveau contenu"
    When I select "Force" from the "1" select of "main_content" slot
    Then I should see "Créer"
    When I fill in "Côté de la force" with "obscur"
    And I submit the widget
    Then I should see "Le côté obscur de la force"
    When I switch to "style" mode
    And I edit the "Force" widget
    Then I should see "Style du widget"
    When I fill in "widget_style[containerBackgroundColor]" with "rgb(255, 0, 0)"
    And I follow the link containing "Mettre à jour"
    And I wait 2 seconds
    And I reload the page
    Then I should see the css property "background-color" of "widget-1" with "rgb(255, 0, 0)"
    When I am on the homepage
    Then I should see the css property "background-color" of "widget-1" with "rgb(255, 0, 0)"
    When I am on "/fr/victoire-dcms/template/show/1"
    And I switch to "style" mode
    And I edit the "Force" widget
    Then I should see "Style du widget"
    When I fill in "widget_style[containerBackgroundColor]" with "rgb(0, 0, 255)"
    And I follow the link containing "Mettre à jour"
    And I wait 2 seconds
    And I reload the page
    Then I should see the css property "background-color" of "widget-1" with "rgb(0, 0, 255)"
    When I am on the homepage
    Then I should see the css property "background-color" of "widget-1" with "rgb(0, 0, 255)"