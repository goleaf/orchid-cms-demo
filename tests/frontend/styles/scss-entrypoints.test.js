import { readFileSync } from 'node:fs';
import { compile } from 'sass';
import { describe, expect, it } from 'vitest';

const scssEntrypoints = [
    'resources/scss/app.scss',
    'resources/scss/site.scss',
    'resources/scss/orchid/lead-pipeline.scss',
];

const bladeViewsWithoutInlineAssets = [
    'resources/views/site/layout.blade.php',
    'resources/views/site/partials/lead-form.blade.php',
    'resources/views/orchid/school/lead-pipeline.blade.php',
    'resources/views/welcome.blade.php',
];

describe('SCSS asset entrypoints', () => {
    it.each(scssEntrypoints)('compiles %s with Dart Sass', (file) => {
        const result = compile(file, { style: 'compressed' });

        expect(result.css.length).toBeGreaterThan(0);
    });

    it.each(scssEntrypoints)('does not contain Blade interpolation in %s', (file) => {
        const source = readFileSync(file, 'utf8');

        expect(source).not.toContain('{{');
        expect(source).not.toContain('@php');
    });

    it.each(bladeViewsWithoutInlineAssets)('keeps %s free from inline style and script blocks', (file) => {
        const source = readFileSync(file, 'utf8');

        expect(source).not.toMatch(/<style\b/i);
        expect(source).not.toMatch(/<script\b/i);
    });
});
