#!/usr/bin/env node
/**
 * Deterministic Gutenberg editor bundle builder for WM Modular Scale.
 *
 * Compiles `blocks-src/index.js` into an isolated IIFE at `build/index.js` and
 * writes `build/index.asset.php` with `declare(strict_types=1);` + `ABSPATH` guard
 * and a SHA-256 content hash version. Zero external bundler dependencies required.
 */

import { createHash } from 'node:crypto';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const rootDir = dirname( dirname( fileURLToPath( import.meta.url ) ) );
const srcFile = join( rootDir, 'blocks-src', 'index.js' );
const buildDir = join( rootDir, 'build' );
const outJs = join( buildDir, 'index.js' );
const outAsset = join( buildDir, 'index.asset.php' );

const source = await readFile( srcFile, 'utf8' );
const wrapped = `/* WM Modular Scale — Compiled Gutenberg Blocks Bundle (generated from blocks-src/index.js) */\n(function () {\n'use strict';\n${ source }\n})();\n`;
const versionHash = createHash( 'sha256' ).update( wrapped ).digest( 'hex' ).slice( 0, 20 );

const assetPhp = `<?php
declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

return array(
\t'dependencies' => array(
\t\t'wp-block-editor',
\t\t'wp-blocks',
\t\t'wp-components',
\t\t'wp-element',
\t\t'wp-i18n',
\t\t'wp-server-side-render',
\t),
\t'version'      => '${ versionHash }',
);
`;

await mkdir( buildDir, { recursive: true } );
await writeFile( outJs, wrapped, 'utf8' );
await writeFile( outAsset, assetPhp, 'utf8' );
