var gulp = require('gulp');
var $ = require('gulp-load-plugins')();

const STYLE_SRC_DIR = 'Bundle/UIBundle/Resources/style/';
const STYLE_SRC_DEST = 'Bundle/UIBundle/Resources/public/style';
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

var stylePipe = function(src) {
  return gulp.src(STYLE_SRC_DIR + src)
    .pipe($.sourcemaps.init())
    .pipe($.sass({
      precision: 6,
      indentWidth: 4,
    })).on('error', reportError)
    .pipe($.autoprefixer({ browsers: AUTOPREFIXER_BROWSERS }))
    .pipe($.sourcemaps.write())
    .pipe(gulp.dest(STYLE_SRC_DEST));
}

// Style required by the victoire UI
gulp.task('style-front', function() {
  return stylePipe('front/main-front.scss');
});

// Style required by the styleguide
gulp.task('style-styleguide', function() {
  return stylePipe('styleguide/main-styleguide.scss');
});

// Both stylesheets
gulp.task('styles', ['style-front', 'style-styleguide']);

// Default task
gulp.task('default', ['styles']);

// Watch task
gulp.task('watch', ['default'], function() {
  gulp.watch(STYLE_SRC_DIR + '/front/**/*.scss', ['style-front']);
  gulp.watch(STYLE_SRC_DIR + '/styleguide/**/*.scss', ['style-styleguide']);
});
