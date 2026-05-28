import js from '@eslint/js';
import globals from 'globals';

const testGlobals = {
    afterEach: 'readonly',
    beforeEach: 'readonly',
    describe: 'readonly',
    expect: 'readonly',
    it: 'readonly',
    vi: 'readonly',
};

export default [
    js.configs.recommended,
    {
        files: [
            'resources/js/**/*.js',
            'tests/frontend/**/*.js',
            'vite.config.js',
            'vitest.config.js',
            'eslint.config.js',
            'postcss.config.js',
        ],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                ...globals.browser,
                ...globals.node,
                ...globals.es2024,
            },
        },
        rules: {
            'no-console': ['warn', { allow: ['warn', 'error'] }],
            'no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
        },
    },
    {
        files: ['tests/frontend/**/*.js'],
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.node,
                ...testGlobals,
            },
        },
    },
];
