const webpack = require("webpack");
const path = require("path");
const config = require("./gulp/config");

const UglifyJSPlugin = require("uglifyjs-webpack-plugin");

function createConfig(env) {
  let isProduction, webpackConfig;

  if (env === undefined) {
    env = process.env.NODE_ENV;
  }

  isProduction = env === "production";

  webpackConfig = {
    context: path.join(__dirname, config.src.js),
    entry: {
      app: "./app.js"
    },
    output: {
      path: path.join(__dirname, config.dest.js),
      filename: "[name].js",
      publicPath: "js/",
    },
    devtool: isProduction ? "#source-map" : "#cheap-module-eval-source-map",
    plugins: [
      new webpack.NoEmitOnErrorsPlugin(),
    ],
    mode: isProduction ? "production" : "development",
    watch: true,
    resolve: {
      extensions: [".js"],
      alias: {
      },
    },

    optimization: {
      minimizer: [
        new UglifyJSPlugin({
          uglifyOptions: {
            output: {
              comments: false,
            },
          },
        }),
      ],
    },

    node: {
      Buffer: false,
      process: false,
    },

    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules\/(?!(bootstrap)\/).*/,
          loader: 'babel-loader',
        },
      ],
    },
  };

  if (isProduction) {
    new webpack.LoaderOptionsPlugin({
      minimize: true,
    }),
    new UglifyJSPlugin(
      {},
      {
        output: { comments: false },
        compress: {
          warnings: false, // remove warnings
          drop_console: true, // Drop console statements
        },
      }
    ),
    new webpack.NoEmitOnErrorsPlugin();
  }

  return webpackConfig;
}
module.exports = createConfig();
module.exports.createConfig = createConfig;
