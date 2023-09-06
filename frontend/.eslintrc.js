module.exports = {
  root: true,
  parser: '@babel/eslint-parser',
  parserOptions: {
    sourceType: 'module'
  },
  env: {
    es6: true,
    node: true,
    browser: true
  },
  extends: [
    'standard'
  ],
  // required to lint *.vue files
  plugins: [
    'html'
  ],
  // check if imports actually resolve
  settings: {
    'import/resolver': {
      webpack: {
        config: 'webpack.config.js'
      }
    }
  },
  // add your custom rules here
  rules: {
    // allow debugger during development
    'no-debugger': process.env.NODE_ENV === 'production' ? 2 : 0,
    'no-console': 0,
    'no-unused-vars': 0,
    'no-unused-expressions': 0,
    'no-undef': 0,
    'no-trailing-spaces': 0,
    'no-param-reassign': ['error', { props: false }],
    'no-duplicate-imports': 0,
    indent: ['error', 2],
    quotes: [2, 'single']
  }
}
