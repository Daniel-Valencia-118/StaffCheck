import js from '@eslint/js';
import globals from 'globals';
import react from 'eslint-plugin-react';
import reactHooks from 'eslint-plugin-react-hooks';

export default [
    { ignores: ['public/**', 'vendor/**', 'node_modules/**', 'bootstrap/ssr/**'] },
    js.configs.recommended,
    react.configs.flat.recommended,
    react.configs.flat['jsx-runtime'],
    {
        files: ['resources/**/*.{js,jsx}'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                ...globals.browser,
                route: 'readonly', // Ziggy, inyectado por @routes
            },
        },
        plugins: { 'react-hooks': reactHooks },
        settings: { react: { version: 'detect' } },
        rules: {
            'react/prop-types': 'off',
            'react-hooks/rules-of-hooks': 'error',
            'react-hooks/exhaustive-deps': 'warn',
            'no-unused-vars': ['warn', { varsIgnorePattern: '^(_|React$)', argsIgnorePattern: '^_' }],
        },
    },
];
