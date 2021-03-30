const { src, dest, watch, series } = require('gulp');
const babel = require('gulp-babel');
const rename = require('gulp-rename');
var browserSync = require('browser-sync').create();
var sass = require('gulp-sass');
const cleanCSS = require('gulp-clean-css');
const zip = require('gulp-zip');
const terser = require('gulp-terser');
const autoprefixer = require('gulp-autoprefixer');

// Will compile es6 using babel and minify using terser.
function js() {
    return src('./assets/src/js/*.js', {sourcemaps: true})
        .pipe(babel({
            presets: ['@babel/env']
        }))
        .pipe(terser())
        .pipe(rename({ extname: '.min.js' }))
        .pipe(dest('./assets/dist/js', {sourcemaps: true}));
}

// Will compile sass to css and minify.
function css() {
    return src("./assets/src/sass/*.scss", {sourcemaps: true})
        .pipe(sass())
        .pipe(autoprefixer({
            cascade: false,
        }))
        .pipe(cleanCSS({compatibility: 'ie8'}))
        .pipe(rename({ extname: '.min.css' }))
        .pipe(dest("./assets/dist/css", {sourcemaps: true}))
        .pipe(browserSync.stream());
}

// Will create a  zip file for plugin which will not contain unnecessary files.
function compress() {
    return src([ '**/*', '!node_modules/**', '!./*.json', '!*.gitignore', '!gulpfile.js', '!.git/**' ]) 
    .pipe(zip('wc-paddle-payment-gateway.zip'))
    .pipe(dest('./'));
}

// Watches for changes
function watchTask() {
    watch('./assets/src/sass/**/*.scss', css);
    watch('./assets/src/js/**/*.js', js)
        .on('change', browserSync.reload);
    watch('./**/*.php')
    .on('change', browserSync.reload);
};

// Opens up the browser
function browser(cb) {
    browserSync.init({
        proxy: "https://personal.local"
    });
    cb();
}

exports.css = css;
exports.js = js;
exports.watch = watchTask;
exports.compress = compress;
exports.browser = browser;
exports.default = series(css, js, browser, watchTask);

// Will create a production build of the plugin.
exports.prod = series(css, js, compress);