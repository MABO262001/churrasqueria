import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

/*
|--------------------------------------------------------------------------
| Configuración Ziggy para localhost y servidor Tecnoweb
|--------------------------------------------------------------------------
|
| Localhost:
|   http://127.0.0.1:8000
|   http://localhost:8000
|
| Servidor:
|   https://www.tecnoweb.org.bo/inf513/grupo17sc/proyecto2
|
| Nota:
| No duplicamos la subcarpeta en JS. Tomamos el objeto Ziggy generado
| por @routes y solo corregimos el location actual.
|
*/

const isLocalhost = ['localhost', '127.0.0.1'].includes(window.location.hostname);

const productionBasePath = '/inf513/grupo17sc/proyecto2';

const normalizeUrl = (value) => String(value || '').replace(/\/+$/, '');

const globalZiggy = window.Ziggy || {};

const ziggyConfig = {
    ...globalZiggy,
    url: isLocalhost
        ? normalizeUrl(window.location.origin)
        : normalizeUrl(`${window.location.origin}${productionBasePath}`),
    location: new URL(window.location.href),
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue')
    ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, ziggyConfig)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
