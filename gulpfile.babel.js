'use strict';

import gulp from 'gulp';
import gulpLoadPlugins from 'gulp-load-plugins';
import webpack from 'webpack';
import webpackConfig from './webpack.config.babel';
const $ = gulpLoadPlugins();

const STYLE_SRC_DIR = 'Bundle/UIBundle/Resources/stylesheets';
const STYLE_DEST_DIR = 'Bundle/UIBundle/Resources/public/stylesheets';
const STYLE_DEST_DIR_WEB = 'Tests/Functionnal/web/bundles/victoireui/stylesheets';

const SCRIPTS_SRC_DIR = 'Bundle/UIBundle/Resources/scripts';
const SCRIPTS_DEST_DIR = 'Bundle/UIBundle/Resources/public/scripts';
const SCRIPTS_DEST_DIR_WEB = 'Tests/Functionnal/web/bundles/victoireui/scripts';

const AUTOPREFIXER_BROWSERS = [
  'ie >= 10',
  'ie_mob >= 10',
  'ff >= 30',
  'chrome >= 34',
  'safari >= 7',
  'opera >= 23',
  'ios >= 7',
  'android >= 4.4',
  'bb >= 10'
];

// For displaying sass compilation errors
var reportError = function(err) {
  $.notify({
    title: 'An error occured with a gulp task',
  }).write(err);

  console.log(err.toString());
  this.emit('end');
}

// Both stylesheets
gulp.task('styles', () => {
    return gulp.src([
        STYLE_SRC_DIR + '/front/main-front.scss',
        STYLE_SRC_DIR + '/styleguide/main-styleguide.scss'
    ])
        .pipe($.sourcemaps.init())
        .pipe($.sass({ precision: 6 })).on('error', reportError)
        .pipe($.autoprefixer({ browsers: AUTOPREFIXER_BROWSERS }))
        .pipe($.sourcemaps.write())
        .pipe($.size())
        .pipe(gulp.dest(STYLE_DEST_DIR))
        .pipe(gulp.dest(STYLE_DEST_DIR_WEB)); // In order to not run `npm run assets` everytime a style gulp task is executed

});

gulp.task('webpack', function(callback) {
    var myConfig = Object.create(webpackConfig);
    myConfig.plugins = [
        new webpack.optimize.DedupePlugin(),
        new webpack.optimize.UglifyJsPlugin()
    ];

    // run webpack
    webpack(myConfig, function(err, stats) {
        if (err) throw new $.util.PluginError('webpack', err);
        $.util.log('[webpack]', stats.toString({
            colors: true,
            progress: true
        }));
        callback();
    });
});

gulp.task('scripts', ['webpack'], () => {
    gulp.src(`${SCRIPTS_DEST_DIR}/victoire.bundle.js`)
        .pipe(gulp.dest(SCRIPTS_DEST_DIR_WEB));
});


// Default task
gulp.task('default', ['styles', 'scripts']);

// Watch task
gulp.task('watch', ['default'], () => {
  gulp.watch(SCRIPTS_SRC_DIR + '/**/*.js', ['scripts']);
  gulp.watch(STYLE_SRC_DIR + '/**/*.scss', ['styles']);
});
