import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export const adminViteEntries = [
    'packages/NewsTech/Admin/Resources/assets/css/app.css',
    'packages/NewsTech/Admin/Resources/assets/js/app.js',
];

export const adminViteRefreshPaths = [
    'packages/NewsTech/Admin/Resources/views/**',
    'packages/NewsTech/Admin/Routes/**',
    'packages/NewsTech/Core/Resources/views/**',
];

export const adminViteBuildDirectory = 'build-admin';
export const adminViteHotFile = 'public/admin.hot';

export function createAdminViteConfig({ standalone = true } = {}) {
    return defineConfig({
        plugins: [
            vue(),
            laravel({
                input: adminViteEntries,
                refresh: adminViteRefreshPaths,
                ...(standalone
                    ? {
                          buildDirectory: adminViteBuildDirectory,
                          hotFile: adminViteHotFile,
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

export default createAdminViteConfig();
