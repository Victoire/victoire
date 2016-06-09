#UPGRADE 1.7

## v1.7.0

Layouts architecture were a piece of shit... this version cleans up but also breaks things and you need to follow these steps to fix your project:

- don't override `VictoireCoreBundle:Layout:layout.html.twig` directly but `VictoireCoreBundle:Layout:defaultLayout.html.twig`
- the initial `VictoireCoreBundle:Layout:fullWidth.html.twig` layout is now known as `VictoireCoreBundle:Layout:defaultLayout.html.twig`
- the `VictoireCoreBundle:Layout:frontLayout.html.twig` does not exist anymore
- the `VictoireCoreBundle:Layout:base.html.twig`'s blocks were removed:
    - foot_script_additional
    - javascript
- the `VictoireCoreBundle:Layout:base.html.twig` has some new blocks:
    - victoire_ui
    - body_header
    - body_content
    - body_footer
- the `VictoireCoreBundle:Layout:layout.html.twig`:
    - has a new `body_content_main` block
    - wrap the `body_content_main` in a main#content tag
    - declare the **main_content** `cms_slot_widgets` (in the `body_content_main` block)
- the `fos_js_routes.js` is now generated with the `prod` suffix in `prod` environment