<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
// Asumiendo que InputError.vue está en resources/js/components/InputError.vue
import InputError from '@/components/InputError.vue';

// Importa los componentes de Shadcn UI 'Input' y 'Label' desde 'components/ui'
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button'; // Para el botón de "Registrar Pago"

import { Head, usePage, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Lucide Vue Next Icons
import { CirclePlus } from 'lucide-vue-next'; // Para el ícono del botón

// --- Definiciones de Tipos (Interfaces) ---
// Estas interfaces son las mismas que en Index.vue para mantener consistencia

interface Cliente {
    id: number;
    nombre: string;
}

interface Proveedor {
    id: number;
    nombre: string;
}

interface Venta {
    id: number;
    total: number;
    cliente_id: number | null;
    cliente?: Cliente | null;
}

interface Compra {
    id: number;
    total: number;
    proveedor_id: number | null;
    proveedor?: Proveedor | null;
}

interface PaymentForm {
    venta_id: number | string;
    compra_id: number | string;
    monto: number | string;
    metodo_pago: string;
    fecha_pago: string;
    referencia_pago: string | undefined; // `undefined` para v-model en inputs de texto opcionales
}

interface BreadcrumbItem {
    label: string;
    link: string;
}

// --- Fin Definiciones de Tipos ---

const page = usePage();

const props = defineProps<{
    ventas: Venta[]; // Lista de ventas disponibles para asociar el pago
    compras: Compra[]; // Lista de compras disponibles para asociar el pago
}>();

// Estado inicial del formulario
const initialFormState: PaymentForm = {
    venta_id: '',
    compra_id: '',
    monto: '',
    metodo_pago: '',
    fecha_pago: new Date().toISOString().slice(0, 16), // Fecha y hora actual por defecto
    referencia_pago: undefined,
};

// newPaymentForm es un ref que contiene el estado del formulario
// Si hay errores de validación, Inertia.js devuelve los datos del formulario en page.props.form
const newPaymentForm = ref<PaymentForm>({
    ...initialFormState,
    // Copiamos las props del formulario que puedan venir de un error de validación previo.
    ...(page.props.form as Partial<Omit<PaymentForm, 'referencia_pago'>> || {}),
    referencia_pago: (page.props.form as any)?.referencia_pago ?? undefined
});

// Watcher para asegurar que solo se selecciona venta_id o compra_id
watch(() => newPaymentForm.value.venta_id, (newValue) => {
    if (newValue) {
        newPaymentForm.value.compra_id = '';
    }
});

watch(() => newPaymentForm.value.compra_id, (newValue) => {
    if (newValue) {
        newPaymentForm.value.venta_id = '';
    }
});

const submit = () => {
    // Validación adicional a nivel de frontend antes de enviar
    if (!newPaymentForm.value.venta_id && !newPaymentForm.value.compra_id) {
        alert('Debe asociar el pago a una Venta o a una Compra.');
        return;
    }

    router.post(route('pagos.store'), newPaymentForm.value, {
        onSuccess: () => {
            // Redirige al índice de pagos después de un registro exitoso
            router.visit(route('pagos.index'), {
                preserveScroll: true,
                preserveState: false, // Asegura que el formulario se resetee completamente
                onSuccess: () => {
                    // Opcional: mostrar un mensaje flash de éxito en la página de índice
                    page.props.flash = { success: 'Pago registrado exitosamente.' };
                }
            });
        },
        onError: (errors) => {
            console.error('Errores al crear pago:', errors);
            // Inertia automáticamente maneja los errores y los hace accesibles via page.props.errors
        },
        onFinish: () => {
            // Esto se ejecuta al finalizar, ya sea éxito o error.
            // No resetear aquí si queremos que los datos y errores persistan en el formulario.
        }
    });
};

const breadcrumbItems = ref<BreadcrumbItem[]>([
    { label: 'Inicio', link: route('dashboard') },
    { label: 'Pagos', link: route('pagos.index') },
    { label: 'Registrar Pago', link: route('pagos.create') }
]);
</script>

<template>
    <Head title="Registrar Pago" />

    <AppLayout :breadcrumb-items="breadcrumbItems">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Registrar Nuevo Pago</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-semibold text-lg text-gray-800 leading-tight mb-4">Detalles del Pago</h3>
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <Label for="venta_id">Asociar a Venta (Opcional)</Label>
                                <select
                                    id="venta_id"
                                    v-model="newPaymentForm.venta_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">Seleccione una Venta</option>
                                    <option v-for="venta in ventas" :key="venta.id" :value="venta.id">
                                        Venta #{{ venta.id }} - Cliente: {{ venta.cliente ? venta.cliente.nombre : 'N/A' }} - Total: ${{ venta.total.toFixed(2) }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="(page.props.errors as Record<string, string>).venta_id" />
                            </div>

                            <div class="mb-4">
                                <Label for="compra_id">Asociar a Compra (Opcional)</Label>
                                <select
                                    id="compra_id"
                                    v-model="newPaymentForm.compra_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">Seleccione una Compra</option>
                                    <option v-for="compra in compras" :key="compra.id" :value="compra.id">
                                        Compra #{{ compra.id }} - Proveedor: {{ compra.proveedor ? compra.proveedor.nombre : 'N/A' }} - Total: ${{ compra.total.toFixed(2) }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="(page.props.errors as Record<string, string>).compra_id" />
                            </div>

                            <div class="mt-4">
                                <Label for="monto">Monto del Pago</Label>
                                <Input
                                    id="monto"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full"
                                    v-model="newPaymentForm.monto"
                                    required
                                    autocomplete="monto-pago"
                                />
                                <InputError class="mt-2" :message="(page.props.errors as Record<string, string>).monto" />
                            </div>

                            <div class="mt-4">
                                <Label for="metodo_pago">Método de Pago</Label>
                                <select
                                    id="metodo_pago"
                                    v-model="newPaymentForm.metodo_pago"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="">Seleccione un método</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                    <option value="tarjeta_debito">Tarjeta de Débito</option>
                                </select>
                                <InputError class="mt-2" :message="(page.props.errors as Record<string, string>).metodo_pago" />
                            </div>

                            <div class="mt-4">
                                <Label for="fecha_pago">Fecha y Hora del Pago</Label>
                                <Input
                                    id="fecha_pago"
                                    type="datetime-local"
                                    class="mt-1 block w-full"
                                    v-model="newPaymentForm.fecha_pago"
                                    required
                                />
                                <InputError class="mt-2" :message="(page.props.errors as Record<string, string>).fecha_pago" />
                            </div>

                            <div class="mt-4">
                                <Label for="referencia_pago">Referencia del Pago (Opcional)</Label>
                                <Input
                                    id="referencia_pago"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="newPaymentForm.referencia_pago"
                                />
                                <InputError class="mt-2" :message="(page.props.errors as Record<string, string>).referencia_pago" />
                            </div>

                            <div class="flex items-center justify-end mt-4 space-x-2">
                                <Button type="submit" :disabled="page.props.processing as boolean">
                                    <CirclePlus class="mr-2 h-4 w-4" />
                                    Registrar Pago
                                </Button>
                                <Link :href="route('pagos.index')">
                                    <Button variant="outline">
                                        Cancelar
                                    </Button>
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
