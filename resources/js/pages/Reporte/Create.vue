<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue'; // Assuming these are still in Components

// Breadcrumbs for AppLayout
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reportes', href: route('reportes.index') },
    { title: 'Crear', href: route('reportes.create'), current: true },
];

const form = useForm({
    tipo: 'ventas', // Default value
    fecha_inicio: '',
    fecha_fin: '',
    filtros: {}, // Initialize as an empty object for dynamic filters
    descripcion: '',
});

const submit = () => {
    form.post(route('reportes.store'), {
        onSuccess: () => {
            router.visit(route('reportes.index')); // Redirect to reports index on success
        },
        onError: (errors) => {
            console.error('Error al crear el reporte:', errors);
        },
    });
};

// Function to reset filters when report type changes
const resetFilters = () => {
    form.filtros = {};
};
</script>

<template>
    <Head title="Crear Reporte" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
            <div class="w-full max-w-4xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Crear Nuevo Reporte</h1>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="tipo">Tipo de Reporte</label>
                                <select
                                    id="tipo"
                                    v-model="form.tipo"
                                    @change="resetFilters"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                >
                                    <option value="ventas">Ventas</option>
                                    <option value="compras">Compras</option>
                                    <option value="inventario">Inventario</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.tipo" />
                            </div>

                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="fecha_inicio">Fecha de Inicio</label>
                                <input
                                    id="fecha_inicio"
                                    type="date"
                                    v-model="form.fecha_inicio"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                />
                                <InputError class="mt-2" :message="form.errors.fecha_inicio" />
                            </div>

                            <div>
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="fecha_fin">Fecha de Fin</label>
                                <input
                                    id="fecha_fin"
                                    type="date"
                                    v-model="form.fecha_fin"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                />
                                <InputError class="mt-2" :message="form.errors.fecha_fin" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="descripcion">Descripción (Opcional)</label>
                                <textarea
                                    id="descripcion"
                                    v-model="form.descripcion"
                                    rows="3"
                                    class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                    placeholder="Añade una descripción para este reporte..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.descripcion" />
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-zinc-700 p-4 rounded-lg" v-if="form.tipo">
                            <h3 class="font-semibold text-lg mb-3 text-gray-800 dark:text-gray-200">Filtros Específicos para {{ form.tipo.charAt(0).toUpperCase() + form.tipo.slice(1) }}</h3>

                            <div v-if="form.tipo === 'ventas'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="cliente_id">ID del Cliente</label>
                                    <input
                                        id="cliente_id"
                                        type="number"
                                        v-model="form.filtros.cliente_id"
                                        class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                        placeholder="Ej. 1"
                                    />
                                    <InputError class="mt-2" :message="form.errors['filtros.cliente_id']" />
                                </div>
                                <div>
                                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="producto_id_ventas">ID del Producto</label>
                                    <input
                                        id="producto_id_ventas"
                                        type="number"
                                        v-model="form.filtros.producto_id"
                                        class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                        placeholder="Ej. 101"
                                    />
                                    <InputError class="mt-2" :message="form.errors['filtros.producto_id']" />
                                </div>
                            </div>

                            <div v-else-if="form.tipo === 'compras'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="proveedor_id">ID del Proveedor</label>
                                    <input
                                        id="proveedor_id"
                                        type="number"
                                        v-model="form.filtros.proveedor_id"
                                        class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                        placeholder="Ej. 20"
                                    />
                                    <InputError class="mt-2" :message="form.errors['filtros.proveedor_id']" />
                                </div>
                                <div>
                                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="producto_id_compras">ID del Producto</label>
                                    <input
                                        id="producto_id_compras"
                                        type="number"
                                        v-model="form.filtros.producto_id"
                                        class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                        placeholder="Ej. 101"
                                    />
                                    <InputError class="mt-2" :message="form.errors['filtros.producto_id']" />
                                </div>
                            </div>

                            <div v-else-if="form.tipo === 'pedidos'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="estado_pedido">Estado del Pedido</label>
                                    <select
                                        id="estado_pedido"
                                        v-model="form.filtros.estado"
                                        class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                    >
                                        <option value="pendiente">Pendiente</option>
                                        <option value="procesando">Procesando</option>
                                        <option value="completado">Completado</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors['filtros.estado']" />
                                </div>
                            </div>

                            <div v-else-if="form.tipo === 'inventario'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="producto_id_inventario">ID del Producto</label>
                                    <input
                                        id="producto_id_inventario"
                                        type="number"
                                        v-model="form.filtros.producto_id"
                                        class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                        placeholder="Ej. 101"
                                    />
                                    <InputError class="mt-2" :message="form.errors['filtros.producto_id']" />
                                </div>
                                <div>
                                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2" for="tipo_movimiento">Tipo de Movimiento</label>
                                    <select
                                        id="tipo_movimiento"
                                        v-model="form.filtros.tipo_movimiento"
                                        class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"
                                    >
                                        <option value="entrada">Entrada</option>
                                        <option value="salida">Salida</option>
                                        <option value="ajuste">Ajuste</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors['filtros.tipo_movimiento']" />
                                </div>
                            </div>

                            <p v-else class="text-sm text-gray-500 dark:text-gray-400 mt-4">
                                No hay filtros específicos para este tipo de reporte.
                            </p>
                        </div>

                        <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
                            <button
                                type="submit"
                                class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">Generando...</span>
                                <span v-else>Crear Reporte</span>
                            </button>
                            <button
                                type="button"
                                @click="router.get(route('reportes.index'))"
                                class="flex-1 md:flex-none px-6 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-all"
                            >
                                Cancelar
                            </button>
                        </div>
                        <div v-if="form.hasErrors" class="mt-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded-lg">
                            <h3 class="font-semibold mb-2">Se encontraron los siguientes errores:</h3>
                            <ul class="list-disc list-inside">
                                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                            </ul>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
