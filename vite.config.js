import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        cssTarget: 'chrome87',
    },
    
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/guest-complete.js', 'resources/js/checkin-scanner.js'],
            refresh: true,
        }),
    ],

});
