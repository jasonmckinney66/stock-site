// required
var gulp = require('gulp');
var concat = require('gulp-concat');
var cleanCSS = require('gulp-clean-css');
var rename = require("gulp-rename");
var sass = require('gulp-sass')(require('sass'));
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');
var rev = require('gulp-rev');

// Dev CSS
gulp.task('default', function(dv) {
  gulp.src(['./src/style/*.scss'])
      .pipe(sourcemaps.init())
      .pipe(sass())
      .pipe(concat('main.css'))
      .pipe(sourcemaps.write('.'))
      .pipe(gulp.dest('./src/style/'));
      dv();
});

// Prod CSS Build
gulp.task('build', function(pd) {
  return gulp.src('./src/style/main.css')
    .pipe(cleanCSS())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('./css/')) // copy original assets

    .pipe(rev())
    .pipe(gulp.dest('./css/')) // write rev'd assets

    .pipe(rev.manifest())
    .pipe(gulp.dest('./css/')) // write manifest
    pd();
});

// Minify JS
gulp.task('scripts', function(sc) {
  gulp.src(['./src/js/vendor/jquery.navpoints.js','./src/js/vendor/tweet-highlighted.js','./src/js/vendor/modernizr.js','./src/js/vendor/jquery.masonry.js','./src/js/app.js'])
    .pipe(concat('app.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('./js/'))
    sc();
});

// Watch tasks
gulp.task('watch', function(wa) {
  gulp.watch('./src/style/custom.scss', gulp.series('default'));
  gulp.watch('./js/app.js', gulp.series('scripts'));
  wa();
});
