import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import legacy from '@vitejs/plugin-legacy';

const viteInputs = [
    'resources/scss/app.scss',
    'resources/scss/site.scss',
    'resources/scss/orchid/lead-pipeline.scss',
    'resources/js/app.js',
    'resources/js/site.js',
    'resources/js/orchid/lead-pipeline.js',
];

export default defineConfig({
    plugins: [
        laravel({
            input: viteInputs,
            refresh: true,
        }),
        legacy({
            targets: ['defaults', 'Firefox ESR', 'not IE 11'],
        }),
        tailwindcss(),
    ],
    build: {
        cssTarget: 'chrome61',
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
