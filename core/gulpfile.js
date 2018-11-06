var gulp = require('gulp');
var sass = require('gulp-sass');
var cleanCSS = require('gulp-clean-css');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');

const jsSrc = [
  'assets/js/admin.js',
  'assets/js/overlay-editor.js'
];

gulp.task('css', function () {
  return gulp.src('assets/css/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(cleanCSS({compatibility: 'ie8'}))
    .pipe(gulp.dest('assets/css'));
});

gulp.task('scripts', function () {
  return gulp.src(jsSrc)
    .pipe(rename({suffix: '.min'}))
    .pipe(uglify())
    .pipe(gulp.dest('assets/js'));
});


gulp.task('watch', function() {
  gulp.watch(['assets/css/*.scss'], ['css']);
  gulp.watch(jsSrc, ['scripts']);
});

gulp.task('default', ['css', 'scripts']);
