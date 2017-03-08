var path = require('path');
var webpack = require('webpack');

const baseConfig = {
    entry: '../scripts/main.js',
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                loader: 'babel-loader',
                query: {
                    'presets': [require('babel-preset-es2015')],
                    'plugins': [require('babel-plugin-array-includes').default],
                }
            }
        ]
    }
}

const bundleConfig = {
    output: {
        path: path.join(__dirname, '../public/scripts'),
        filename: 'victoire.bundle.js',
        chunkFilename: '[id].bundle.js'
    },
    plugins:[
        new webpack.optimize.UglifyJsPlugin()
    ]
}

const devConfig = {
    output: {
        path: path.join(__dirname, '../../../../Tests/Functionnal/web/bundles/victoireui/scripts'),
        filename: 'victoire.bundle.js',
        chunkFilename: '[id].bundle.js'
    }
}

const styleguideConfig = {
    entry: '../scripts/styleguide/main.js',
    output: {
        path: path.join(__dirname, '../public/scripts'),
        filename: 'styleguide.bundle.js',
        chunkFilename: '[id].bundle.js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                loader: 'babel-loader',
                query: {
                    'presets': [require('babel-preset-es2015')],
                    'plugins': [require('babel-plugin-array-includes').default],
                }
            }
        ]
    },
    plugins:[
        new webpack.optimize.UglifyJsPlugin()
    ]
};

module.exports = [
    Object.assign({}, baseConfig, bundleConfig),
    Object.assign({}, baseConfig, devConfig),
    styleguideConfig
];
