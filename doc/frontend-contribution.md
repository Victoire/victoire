# Frontend contribution

## Work with the stylesheets
All the stylesheets required for victoire are written in scss into the `Bundle/UIBundle/Resources/stylesheets` folder. You will find two seperate files :
* `/front` for the victoire UI itself
* `/styleguide` for the the styleguide UI (not the visual components detailed into the styleguide)

The Sass compilation is managed with gulp, you can run the following commands to transform the stylesheets in css :
```sh
# Compile once
(cd Bundle/UIBundle/Resources/config && gulp)

# Compile with watch
(cd Bundle/UIBundle/Resources/config && gulp watch)
```

In order to keep consistent stylesheets, please use [.scss-lint.yml](Bundle/UIBundle/Resources/stylesheets/.scss-lint.yml) with your IDE.

## Work with the javascript
All the root javascript files are located into the folder `Bundle/UIBundle/Resources/scripts`. Those files are bundled with Webpack, and they also are transpiled into es2015 with Babel via Webpack.

You can run the webpack build with the following commands :
```sh
# Bundle once
(cd Bundle/UIBundle/Resources/config && webpack)

# Bundle with watch
(cd Bundle/UIBundle/Resources/config && webpack --watch)
```