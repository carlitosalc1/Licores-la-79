import '../css/app.css';

// Importaciones principales
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';

// Importaciones para Toastification
import Toast, { type PluginOptions, POSITION } from 'vue-toastification';
import 'vue-toastification/dist/index.css'; // CSS de Toastification

// Importaciones para Chart.js
import { Chart, registerables } from 'chart.js';

// Corrección de la declaración de módulo para Vite
declare module 'vite' {
  interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
    [key: string]: string | boolean | undefined;
  }

  interface ImportMeta {
    readonly env: ImportMetaEnv;
    readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
  }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Registrar los componentes de Chart.js globalmente
Chart.register(...registerables);

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const toastOptions: PluginOptions = {
      position: POSITION.TOP_RIGHT, // Usar el valor enumerado de POSITION
      timeout: 3000, // 3 segundos
      closeOnClick: true,
      pauseOnFocusLoss: true,
      pauseOnHover: true,
      draggable: true,
      draggablePercent: 0.6,
      showCloseButtonOnHover: false,
      hideProgressBar: false,
      closeButton: 'button',
      icon: true,
      rtl: false,
    };

    createApp({ render: () => h(App, props) })
      .use(plugin) // Plugin de Inertia
      .use(ZiggyVue) // Plugin de Ziggy
      .use(Toast, toastOptions) // Plugin de Toastification con opciones
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});

// Configura el tema claro/oscuro al cargar la página
initializeTheme();
