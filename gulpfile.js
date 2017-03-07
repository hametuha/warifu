var gulp        = require('gulp'),
    fs          = require('fs'),
    $           = require('gulp-load-plugins')(),
    pngquant    = require('imagemin-pngquant'),
    saveLicense = require('uglify-save-license'),
    eventStream = require('event-stream');


// Sass tasks
gulp.task('sass', function () {
  return gulp.src(['./src/sass/**/*.scss'])
    .pipe($.plumber({
      errorHandler: $.notify.onError('<%= error.message %>')
    }))
    .pipe($.sourcemaps.init({loadMaps: true}))
    .pipe($.sassBulkImport())
    .pipe($.sass({
      errLogToConsole: true,
      outputStyle    : 'compressed',
      includePaths   : [
        './src/sass'
      ]
    }))
    .pipe($.autoprefixer({browsers: ['last 2 version', '> 5%']}))
    .pipe($.sourcemaps.write('./map'))
    .pipe(gulp.dest('./assets/css'));
});


// Minify All
gulp.task('js', function () {
  return gulp.src(['./src/js/*.js', '!./src/js/lib.js'])
    .pipe($.sourcemaps.init({
      loadMaps: true
    }))
    .pipe($.include({
      extensions: "js",
      hardFail: true,
      includePaths: [
        __dirname + "/src/js"
      ]
    }))
    .pipe($.uglify({
      reserveComments: saveLicense
    }))
    .on('error', $.util.log)
    .pipe($.sourcemaps.write('./map'))
    .pipe(gulp.dest('./assets/js/'));
});


// JS Hint
gulp.task('jshint', function () {
  return gulp.src(['./src/js/**/*.js'])
    .pipe($.jshint('./src/.jshintrc'))
    .pipe($.jshint.reporter('jshint-stylish'));
});

// Build libraries
gulp.task('copylib', function () {
  return eventStream.merge(

    gulp.src(['./src/js/lib.js'])
      .pipe($.sourcemaps.init({
        loadMaps: true
      }))
      .pipe($.include({
        extensions: "js",
        hardFail: true,
        includePaths: [
          __dirname + "/bower_components"
        ]
      }))
      .pipe($.uglify({
        reserveComments: saveLicense
      }))
      .on('error', $.util.log)
      .pipe($.sourcemaps.write('./map'))
      .pipe(gulp.dest('./assets/js/')),

    // Copy MDI
    gulp.src([
      'bower_components/lumx/dist/fonts/**/*'
    ])
      .pipe(gulp.dest('./assets/fonts/')),

    // Copy Lumx
    gulp.src([
      'bower_components/lumx/dist/lumx.css'
    ])
      .pipe(gulp.dest('./assets/'))

  );
});

// Image min
gulp.task('imagemin', function () {
  return gulp.src('./src/img/**/*')
    .pipe($.imagemin({
      progressive: true,
      svgoPlugins: [{removeViewBox: false}],
      use        : [pngquant()]
    }))
    .pipe(gulp.dest('./assets/img'));
});


// watch
gulp.task('watch', function () {
  // Make SASS
  gulp.watch('./src/sass/**/*.scss', ['sass']);
  // JS
  gulp.watch(['./src/js/**/*.js'], ['js', 'jshint']);
  // Minify Image
  gulp.watch('./src/img/**/*', ['imagemin']);
});


// Build
gulp.task('build', ['copylib', 'js', 'sass', 'imagemin']);

// Default Tasks
gulp.task('default', ['watch']);
