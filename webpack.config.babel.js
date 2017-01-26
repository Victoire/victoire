var path = require('path');
var webpack = require('webpack');

module.exports = {
    entry: {
        victoire: './Bundle/UIBundle/Resources/scripts/main.js'
    },
    output: {
        path: path.join(__dirname, 'Bundle/UIBundle/Resources/public/scripts'),
        filename: '[name].bundle.js',
        chunkFilename: '[id].bundle.js'
    },
    module: {
        loaders: [
            {
                loader: "babel-loader",
                test: path.join(__dirname, 'Bundle/UIBundle/Resources/scripts'),
                query: {
                    plugins: [],
                    presets: 'es2015'
                }
            }
        ]
    }
};
