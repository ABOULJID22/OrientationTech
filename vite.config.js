import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
/* import filament from 'filament-vite-plugin'; */
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css',
            ],           
            refresh: true,
        }),
        
       /*  filament({
            // permet à Filament de compiler ses assets + ton thème
            themes: [
                'resources/css/filament/admin/theme.css',
            ],
        }), */
    ],
});
