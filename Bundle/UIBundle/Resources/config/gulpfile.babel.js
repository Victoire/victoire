'use strict';

import gulp from 'gulp';
import gulpLoadPlugins from 'gulp-load-plugins';
import webpack from 'webpack';
import webpackConfig from './webpack.config';
const $ = gulpLoadPlugins();

const style_src_dir = '../stylesheets';
const style_dest_dir = '../public/stylesheets';
const style_dest_dir_web = '../../../../Tests/Functionnal/web/bundles/victoireui/stylesheets';

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
        style_src_dir + '/front/main-front.scss',
        style_src_dir + '/styleguide/main-styleguide.scss'
    ])
        .pipe($.sourcemaps.init())
        .pipe($.sassGlob())
        .pipe($.sass({ precision: 6 })).on('error', reportError)
        .pipe($.autoprefixer({ browsers: [
            'ie >= 10',
            'ie_mob >= 10',
            'ff >= 30',
            'chrome >= 34',
            'safari >= 7',
            'opera >= 23',
            'ios >= 7',
            'android >= 4.4',
            'bb >= 10'
        ]}))
        .pipe($.sourcemaps.write())
        .pipe($.size())
        .pipe(gulp.dest(style_dest_dir))
        .pipe(gulp.dest(style_dest_dir_web)); // In order to not run `npm run assets` everytime a style gulp task is executed

});

// Default task
gulp.task('default', ['styles']);

// Watch task
gulp.task('watch', ['default'], () => {
  gulp.watch(style_src_dir + '/**/*.scss', ['styles']);
});
