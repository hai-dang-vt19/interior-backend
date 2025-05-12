import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/scss/app.scss',
                'resources/js/app.js',
                'resources/scss/custom.scss',
                'resources/scss/_variable.scss'
            ],
            refresh: true,
        }),
    ],
    // resolve: {
    //     alias: {
    //         '@': path.resolve(__dirname, './resources'),
    //         '~': path.resolve(__dirname, './node_modules'),
    //     }
    // },
});
