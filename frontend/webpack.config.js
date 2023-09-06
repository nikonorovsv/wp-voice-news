const webpack = require('webpack')
const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const ESLintPlugin = require('eslint-webpack-plugin')

let config = {
  externals: {
    ajaxurl: 'ajaxurl'
  },
  entry: [
    './src/js/main.js',
    './src/scss/main.scss'
  ],
  output: {
    filename: 'main.js',
    path: path.resolve(__dirname, 'dist')
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: [
          { loader: 'babel-loader' }
        ]
      },
      {
        test: /\.(css|scss)$/,
        use: [
          { loader: MiniCssExtractPlugin.loader },
          { loader: 'css-loader', options: { sourceMap: true } },
          { loader: 'resolve-url-loader' },
          { loader: 'postcss-loader' },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true
            }
          }
        ]
      },
      {
        test: /\.(png|jpg|gif|svg)$/,
        type: 'asset/resource'
      },
      {
        test: /\.(ttf|eot|woff|woff2)$/,
        type: 'asset/resource'
      }
    ]
  },
  resolve: {
    extensions: ['.tsx', '.ts', '.js']
  },
  plugins: [
    new ESLintPlugin(),
    new webpack.ProvidePlugin({}),
    new MiniCssExtractPlugin({
      filename: 'main.css'
    })
  ]
}

module.exports = (env, argv) => {
  if (argv.mode === 'development') {
    config = {
      ...config,
      watch: true,
      watchOptions: {
        aggregateTimeout: 100
      },
      devtool: 'inline-cheap-module-source-map',
      devServer: {
        overlay: true
      }
    }
  }

  if (argv.mode === 'production') {
    config = {
      ...config,
      optimization: {
        minimize: true
      }
    }
  }

  return config
}
