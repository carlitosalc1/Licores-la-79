<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, usePage, router, Head } from '@inertiajs/vue3';


// Definimos la interfaz para BreadcrumbItem
interface BreadcrumbItem {
    title: string;
    href: string;
}

// Definimos la interfaz para Cliente
interface Cliente {
    id: number;
    tipo_identificacion: string;
    numero_identificacion: string;
    nombre: string;
    apellido: string;
    direccion: string;
    telefono: string;
    correo: string;
}
// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Clientes', href: '/clientes' },
    { title: 'Editar Cliente', href: '#' },
];

// Obtener el Cliente desde los props de Inertia y tiparlo
const page = usePage<{ cliente?: Cliente }>();  // El "cliente" puede no existir.
const cliente = page.props.cliente;


// Formulario con datos prellenados
const form = useForm({
    tipo_identificacion: cliente?.tipo_identificacion || '',
    numero_identificacion: cliente?.numero_identificacion || '',
    nombre: cliente?.nombre || '',
    apellido: cliente?.apellido || '',
    direccion: cliente?.direccion || '',
    telefono: cliente?.telefono || '',
    correo: cliente?.correo || '',
});

// Envío del formulario (solo PUT para edición)
const submit = () => {
    form.put(`/clientes/${cliente?.id}`, {
        onSuccess: () => router.visit('/clientes')// Redirige al listado

    });
};
</script>

<template>
        <Head title="Editar Cliente" />
     <AppLayout :breadcrumbs="breadcrumbs">
  <div class="min-h-screen flex items-center justify-center p-4 bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-3xl">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Editar Cliente</h1>

        <form @submit.prevent="submit" class="space-y-6">
       <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
     <div>
      <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de identificación</label>
      <select v-model="form.tipo_identificacion" class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all">
      <option disabled value="">Seleccione una opción</option>
      <option value="Cédula de Ciudadanía">Cédula de Ciudadanía</option>
      <option value="Cédula de Extranjería">Cédula de Extranjería</option>
      <option value="Pasaporte">Pasaporte</option>
      </select>
      <div v-if="form.errors.tipo_identificacion" class="text-red-500 text-sm mt-1">
        {{ form.errors.tipo_identificacion }}
      </div>
     </div>

     <div>
          <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Número de identificación</label>
          <input v-model="form.numero_identificacion" type="text" inputmode="numeric" pattern="[0-9]*" @input="form.numero_identificacion = form.numero_identificacion.replace(/\D/g, '')"
            class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"/>
          <div v-if="form.errors.numero_identificacion" class="text-red-500 text-sm mt-2">
            {{ form.errors.numero_identificacion }}
          </div>
        </div>

        <div>
          <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre</label>
          <input v-model="form.nombre" type="text" autocomplete="off"
            class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"/>
          <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-2">
            {{ form.errors.nombre }}
          </div>
        </div>

        <div>
          <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Apellido</label>
          <input v-model="form.apellido" type="text" autocomplete="off"
            class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"/>
          <div v-if="form.errors.apellido" class="text-red-500 text-sm mt-2">
            {{ form.errors.apellido }}
          </div>
        </div>

        <div>
          <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Dirección</label>
          <input v-model="form.direccion" type="text" autocomplete="off"
            class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"/>
          <div v-if="form.errors.direccion" class="text-red-500 text-sm mt-2">
            {{ form.errors.direccion }}
          </div>
        </div>

        <div>
          <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Teléfono</label>
          <input v-model="form.telefono" type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="off"
            @input="form.telefono = form.telefono.replace(/\D/g, '')"
            class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"/>
          <div v-if="form.errors.telefono" class="text-red-500 text-sm mt-2">
            {{ form.errors.telefono }}
          </div>
        </div>

        <div class="md:col-span-2">
          <label class="block font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
          <input v-model="form.correo" type="email" autocomplete="off"
            class="w-full bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg p-3 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-all"/>
          <div v-if="form.errors.correo" class="text-red-500 text-sm mt-2">
            {{ form.errors.correo }}
          </div>
        </div>
      </div>

      <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0 pt-6">
            <button type="submit" class="flex-1 md:flex-none px-6 py-3 rounded-lg bg-cyan-700 hover:bg-cyan-400 text-white font-medium shadow-lg shadow-cyan-500/20 transition-all":disabled="form.processing">
                <span v-if="form.processing">Procesando...</span>
                <span v-else>Actualizar</span>
            </button>
            <button type="button" @click="router.get('/clientes')" class="flex-1 md:flex-none px-6 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-700 dark:hover:bg-gray-500 font-medium transition-all">
                 Cancelar
            </button>
      </div>
    </form>
  </div>
</div>
</div>
</AppLayout>
</template>
