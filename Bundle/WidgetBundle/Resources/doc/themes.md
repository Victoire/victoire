#Themes

Themes in Victoire are differents view for the same kind of a widget.
For exemple, in the ButtonWidget, we are able to customize the size and the type meaning of our button.
But what if your client wants something fancy, for example a shadow or some rounded corners?
Here comes the widget theme concept and the theme will be available in the Style edition tab.

## How to create a new theme?

Let's continue with our RoundedCorner WidgetButton example.

1. think about a name for our theme…

###RoundedCorner!

OK, that's a great name then create the `showRoundedCorner` file in:

- in the `@WidgetButtonBundle/Resources/views` directly if it's a global theme you want to use and reuse and share between projects.
- in `app/Resources/WidgetButtonBundle/views` if instead you'll just use it in this project

2. Add a translation for your theme in the `victoire` domain.

According the scope (global or local) you decided in first part, add the required translations file `victoire.LOCALE.xliff`.
For a global scope, you'll have been asked to create `en`glish and `fr`ench translation files, for local, you obvisouly can do according your own needs:

```xml
<?xml version="1.0" encoding="utf-8"?>
<xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" xmlns:jms="urn:jms:translation" version="1.2">
  <file date="2013-11-27T18:15:06Z" source-language="en" target-language="en" datatype="plaintext" original="not.available">
    <body>
      <trans-unit resname="victoire.VictoireWidgetButtonBundle.theme.RoundedCorner.label">
        <source>victoire.VictoireWidgetButtonBundle.theme.RoundedCorner.label</source>
        <target>Rounded Corners</target>
      </trans-unit>
    </body>
  </file>
</xliff>
```

##Behind the scene

To understand what is done, look this commit: https://github.com/Victoire/victoire/commit/824693be7aad5ebb0ba2cd87246b92eb22156bc4#diff-dc812ef9482ecbcae6ebf322856d3b92R98
