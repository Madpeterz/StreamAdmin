import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [`resources/views/**/*`],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        // dev stack runs Vite in a container; the browser reaches it on localhost
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: { host: 'localhost' },
        // 'vite' is the service name used by the screenshot/browser test container
        allowedHosts: ['localhost', 'vite'],
        // file events do not cross Docker Desktop bind mounts, so the dev container polls (VITE_POLL=1)
        // Polling stats every watched file, and vendor/ alone is tens of thousands of them: it kept Vite at
        // 30% CPU and made the first CSS request after any view edit take ~30 s. Only the app's own sources matter.
        watch: {
            ignored: ['**/vendor/**', '**/storage/**', '**/node_modules/**', '**/.git/**', '**/tests/**', '**/public/**', '**/bootstrap/cache/**'],
            ...(process.env.VITE_POLL ? { usePolling: true, interval: 500 } : {}),
        },
    },
});