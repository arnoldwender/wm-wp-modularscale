// ESLint Flat Config.
//
// Ersetzt die .eslintrc.js dieses Repos. Ab ESLint 9 ist das Flat-Format
// verpflichtend — die alte Datei wurde schlicht nicht mehr gelesen, und
// `npx eslint` brach mit exit 2 ab ("couldn't find an eslint.config file").
// Der Lint lief hier also seit dem Sprung auf ESLint 9 überhaupt nicht.
//
// Bewusst NICHT übernommen aus der alten Konfiguration: die Overrides für
// React und Vue. Dieses Repo enthält null .jsx/.tsx und null .vue-Dateien;
// die Blöcke waren toter Ballast. Für React käme erschwerend hinzu, dass
// eslint-plugin-react (aktuell 7.37.5) bei `eslint ^9.7` deckelt und mit
// ESLint 10 gar nicht installierbar ist.
//
// Regeln 1:1 aus der .eslintrc.js übernommen.
import js from '@eslint/js';
import globals from 'globals';
import tseslint from 'typescript-eslint';
import astroPlugin from 'eslint-plugin-astro';
// astro-eslint-parser@3 hat den Default-Export entfernt → Namespace-Import.
// Der Namespace erfüllt parseForESLint, damit ist ESLint zufrieden.
import * as astroParser from 'astro-eslint-parser';

export default [
  {
    ignores: ['dist/**', '.astro/**', 'node_modules/**', 'coverage/**', 'public/**'],
  },
  js.configs.recommended,
  {
    files: ['**/*.{js,mjs,cjs,ts,tsx,astro}'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ...globals.node,
      },
    },
    rules: {
      'no-console': ['warn', { allow: ['warn', 'error'] }],
      'no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
      'prefer-const': 'warn',
      'no-var': 'error',
    },
  },
  ...tseslint.configs.recommended.map((c) => ({ ...c, files: ['**/*.{ts,tsx,mts,cts}'] })),
  {
    files: ['**/*.{ts,tsx,mts,cts}'],
    rules: {
      '@typescript-eslint/no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
      '@typescript-eslint/no-explicit-any': 'warn',
      // no-unused-vars von ESLint würde hier doppelt melden
      'no-unused-vars': 'off',
    },
  },
  {
    files: ['**/*.astro'],
    plugins: { astro: astroPlugin },
    languageOptions: {
      parser: astroParser,
      parserOptions: {
        parser: tseslint.parser,
        extraFileExtensions: ['.astro'],
      },
      globals: { ...globals.browser, ...globals.node },
    },
    rules: {
      ...astroPlugin.configs.recommended.rules,
    },
  },
];
