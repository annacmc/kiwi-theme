module.exports = {
  env: {
    browser: true,
    es2021: true,
    node: true,
  },
  extends: [
    '@wordpress/eslint-plugin/recommended',
  ],
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module',
  },
  rules: {
    // WordPress specific rules
    'no-console': 'warn',
    'no-unused-vars': 'warn',
  },
};