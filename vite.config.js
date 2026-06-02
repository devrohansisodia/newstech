import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import {
    adminViteEntries,
    adminViteRefreshPaths,
} from './packages/NewsTech/Admin/vite.config.js';
import {
    frontendViteEntries,
    frontendViteRefreshPaths,
} from './packages/NewsTech/Frontend/vite.config.js';

const rootViteEntries = ['resources/css/app.css', 'resources/js/app.js'];

const refreshPaths = [
    'resources/views/**',
    'routes/**',
    ...adminViteRefreshPaths,
    ...frontendViteRefreshPaths,
];

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                ...rootViteEntries,
                ...adminViteEntries,
                ...frontendViteEntries,
            ],
            refresh: [...new Set(refreshPaths)],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
