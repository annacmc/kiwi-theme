module.exports = {
  root: true,
  extends: [
    '@wordpress/eslint-plugin/recommended',
    '@wordpress/eslint-plugin/recommended-with-formatting'
  ],
  env: {
    browser: true,
    node: true,
    es6: true,
    jquery: true
  },
  globals: {
    wp: 'readonly',
    jQuery: 'readonly',
    $: 'readonly'
  },
  rules: {
    // WordPress specific rules
    'no-console': 'warn',
    'no-unused-vars': 'error',
    'no-undef': 'error',
    
    // Code quality rules
    'indent': ['error', 2],
    'quotes': ['error', 'single'],
    'semi': ['error', 'always'],
    'comma-dangle': ['error', 'never'],
    
    // Accessibility rules
    'jsx-a11y/alt-text': 'error',
    'jsx-a11y/anchor-has-content': 'error',
    'jsx-a11y/click-events-have-key-events': 'error',
    'jsx-a11y/no-noninteractive-element-interactions': 'error',
    
    // Security rules
    'no-eval': 'error',
    'no-implied-eval': 'error',
    'no-new-func': 'error',
    'no-script-url': 'error'
  },
  parserOptions: {
    ecmaVersion: 2022,
    sourceType: 'module'
  }
};