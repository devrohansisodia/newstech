import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export const frontendViteEntries = [
    'packages/NewsTech/Frontend/Resources/assets/css/app.css',
    'packages/NewsTech/Frontend/Resources/assets/js/app.js',
];

export const frontendViteRefreshPaths = [
    'packages/NewsTech/Frontend/Resources/views/**',
    'packages/NewsTech/Frontend/Routes/**',
    'packages/NewsTech/Core/Resources/views/**',
];

export const frontendViteBuildDirectory = 'build-frontend';
export const frontendViteHotFile = 'public/frontend.hot';

export function createFrontendViteConfig({ standalone = true } = {}) {
    return defineConfig({
        plugins: [
            laravel({
                input: frontendViteEntries,
                refresh: frontendViteRefreshPaths,
                ...(standalone
                    ? {
                          buildDirectory: frontendViteBuildDirectory,
                          hotFile: frontendViteHotFile,
                      }
                    : {}),
            }),
            tailwindcss(),
        ],
        server: {
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    });
}

export default createFrontendViteConfig();
