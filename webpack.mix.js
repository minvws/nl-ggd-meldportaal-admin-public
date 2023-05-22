const mix = require("laravel-mix");

mix.webpackConfig({
    resolve: {
        fallback: {
            "crypto": false, // or, if the polyfill is needed, use require.resolve("crypto-browserify"),
            "path": false, // or, if the polyfill is needed, require.resolve("path-browserify"),
        }
    }
})

mix.js("resources/js/app.js", "public/js")
