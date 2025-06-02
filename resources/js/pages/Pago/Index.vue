<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import { Pencil, Trash2, CirclePlus } from 'lucide-vue-next';

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
    cliente?: Cliente | null; // Opcional, puede que no siempre se cargue en todas las consultas
}

interface Compra {
    id: number;
    total: number;
    proveedor_id: number | null;
    proveedor?: Proveedor | null; // Opcional, puede que no siempre se cargue

}

interface Pago {
    id: number;
    venta_id: number | null;
    compra_id: number | null;
    monto: number;
    metodo_pago: string;
    fecha_pago: string; // La migración usa timestamp, y datetime-local devuelve string
    referencia_pago: string | null; // El valor que viene del backend puede ser null
    created_at: string;
    updated_at: string;
    venta?: Venta | null; // Relación cargada, puede ser null
    compra?: Compra | null; // Relación cargada, puede ser null
}

interface PaymentForm {
    venta_id: number | string; // 'number' cuando se selecciona, '' cuando vacío
    compra_id: number | string; // 'number' cuando se selecciona, '' cuando vacío
    monto: number | string; // Se inicializa como string para el input type="number", pero puede ser number
    metodo_pago: string;
    fecha_pago: string;
    referencia_pago: string | undefined; // <--- CORRECCIÓN CLAVE: `undefined` en lugar de `null` para `v-model`
}

interface BreadcrumbItem {
    label: string;
    link: string;
}

// --- Fin Definiciones de Tipos ---

const page = usePage(); // usePage retorna un objeto con las props de Inertia

const props = defineProps<{
    pagos: Pago[]; // Lista de pagos existentes
    ventas: Venta[]; // Lista de ventas disponibles para asociar el pago
    compras: Compra[]; // Lista de compras disponibles para asociar el pago
}>();

// Estado inicial del formulario
const initialFormState: PaymentForm = {
    venta_id: '',
    compra_id: '',
    monto: '',
    metodo_pago: '',
    fecha_pago: new Date().toISOString().slice(0, 16), // Fecha y hora actual por defecto (formato 'YYYY-MM-DDTHH:MM')
    referencia_pago: undefined, // <--- Inicializa como `undefined`
};

// newPaymentForm es un ref que contiene el estado del formulario
// Si hay errores de validación, Inertia.js devuelve los datos del formulario en page.props.form
const newPaymentForm = ref<PaymentForm>({
    ...initialFormState,

    // Usamos el operador ?? para asegurar que 'referencia_pago' sea 'undefined' si viene como 'null'.
    ...(page.props.form as Partial<Omit<PaymentForm, 'referencia_pago'>> || {}), // Copia todo excepto referencia_pago para manejarlo aparte
    referencia_pago: (page.props.form as any)?.referencia_pago ?? undefined // Manejo específico para asegurar string | undefined
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

    // Convertir monto a number si es string antes de enviar (opcional, el backend también valida)
    // newPaymentForm.value.monto = parseFloat(newPaymentForm.value.monto as string);

    router.post(route('pagos.store'), newPaymentForm.value, {
        onFinish: () => {
            // Resetear formulario a su estado inicial después de un envío exitoso
            newPaymentForm.value = { ...initialFormState };
        },
        onError: (errors) => {
            console.error('Errores al crear pago:', errors);
            // Inertia automáticamente maneja los errores y los hace accesibles via page.props.errors
        }
    });
};

const searchQuery = ref<string>('');

const filteredPagos = computed<Pago[]>(() => {
    if (!searchQuery.value) {
        return props.pagos;
    }
    const query = searchQuery.value.toLowerCase();
    return props.pagos.filter(pago =>
        (pago.metodo_pago && pago.metodo_pago.toLowerCase().includes(query)) ||
        (pago.referencia_pago && pago.referencia_pago.toLowerCase().includes(query)) ||
        (pago.venta && pago.venta.cliente && pago.venta.cliente.nombre.toLowerCase().includes(query)) ||
        (pago.compra && pago.compra.proveedor && pago.compra.proveedor.nombre.toLowerCase().includes(query)) ||
        (pago.monto !== undefined && pago.monto.toString().includes(query))
    );
});

const destroyPago = (id: number) => {
    router.delete(route('pagos.destroy', id), {
        onSuccess: () => {
            // Opcional: mostrar un mensaje de éxito, Inertia Flash Messages son útiles aquí
            // page.props.flash?.success = 'Pago eliminado exitosamente.';
        },
        onError: (errors) => {
            console.error('Error al eliminar pago:', errors);
            alert('Error al eliminar el pago.');
        }
    });
};

const breadcrumbItems = ref<BreadcrumbItem[]>([
    { label: 'Inicio', link: route('dashboard') },
    { label: 'Pagos', link: route('pagos.index') }
]);
</script>

<template>
    <Head title="Gestión de Pagos" />

    <AppLayout :breadcrumb-items="breadcrumbItems">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Pagos</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-semibold text-lg text-gray-800 leading-tight mb-4">Registrar Nuevo Pago</h3>
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

                            <div class="flex items-center justify-end mt-4">
                                <Button type="submit" :disabled="page.props.processing as boolean">
                                    <CirclePlus class="mr-2 h-4 w-4"/>
                                    Registrar Pago
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-semibold text-lg text-gray-800 leading-tight mb-4">Pagos Registrados</h3>

                        <div class="mb-4">
                            <Input
                                type="text"
                                v-model="searchQuery"
                                placeholder="Buscar pagos por método, referencia, cliente o proveedor..."
                                class="w-full"
                            />
                        </div>

                        <Table>
                            <TableCaption v-if="filteredPagos.length === 0">No se encontraron pagos.</TableCaption>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Monto</TableHead>
                                    <TableHead>Método de Pago</TableHead>
                                    <TableHead>Fecha de Pago</TableHead>
                                    <TableHead>Referencia</TableHead>
                                    <TableHead>Asociado a</TableHead>
                                    <TableHead class="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="pago in filteredPagos" :key="pago.id">
                                    <TableCell class="font-medium">{{ pago.id }}</TableCell>
                                    <TableCell>${{ pago.monto ? pago.monto.toFixed(2) : '0.00' }}</TableCell>
                                    <TableCell>{{ pago.metodo_pago }}</TableCell>
                                    <TableCell>{{ pago.fecha_pago ? new Date(pago.fecha_pago).toLocaleString() : 'N/A' }}</TableCell>
                                    <TableCell>{{ pago.referencia_pago || 'N/A' }}</TableCell>
                                    <TableCell>
                                        <span v-if="pago.venta">Venta #{{ pago.venta.id }} (Cliente: {{ pago.venta.cliente ? pago.venta.cliente.nombre : 'N/A' }})</span>
                                        <span v-else-if="pago.compra">Compra #{{ pago.compra.id }} (Proveedor: {{ pago.compra.proveedor ? pago.compra.proveedor.nombre : 'N/A' }})</span>
                                        <span v-else>N/A</span>
                                    </TableCell>
                                    <TableCell class="text-right flex justify-end space-x-2">
                                        <Link :href="route('pagos.edit', pago.id)">
                                            <Button variant="outline" size="sm">
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button variant="destructive" size="sm">
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>¿Estás absolutamente seguro?</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        Esta acción no se puede deshacer. Esto eliminará permanentemente este pago.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                                                    <AlertDialogAction @click="destroyPago(pago.id)">Eliminar</AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
