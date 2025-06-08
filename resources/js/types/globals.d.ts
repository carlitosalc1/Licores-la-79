// resources/js/types/globals.d.ts

// Esto es para Ziggy (lo que ya tienes y es correcto)
import type { route as routeFn } from 'ziggy-js';
declare global {
    const route: typeof routeFn; // Esta línea DEBE ESTAR dentro de este declare global
}

// --- Inicio de la extensión de tipos para Inertia ---
// Esta sección DEBE ESTAR FUERA DE CUALQUIER OTRO 'declare global'
// Debe estar directamente en el nivel superior del archivo .d.ts

import { PageProps as InertiaPageProps } from '@inertiajs/core';

// Define la estructura de tu objeto flash
interface FlashMessages {
    success?: string;
    error?: string;
    // Agrega aquí cualquier otro tipo de mensaje flash que uses (e.g., info, warning)
}

// Extiende la interfaz PageProps de Inertia
declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps {
        flash?: FlashMessages;
        // Aquí puedes agregar otras propiedades que compartes globalmente con Inertia
        // Por ejemplo, si tienes 'auth' compartido:
        // auth: {
        //     user: { id: number; name: string; email: string; } | null;
        // };
    }
}
// --- Fin de la extensión de tipos para Inertia ---
