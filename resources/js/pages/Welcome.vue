<template>
  <div
    class="min-h-screen bg-cover bg-center flex flex-col items-center justify-center px-4 text-center"
    style="background-image: url('/img/fondo-bienvenida.png')"
  >
    <!-- Fondo blanco translúcido -->
    <div class="bg-white/90 p-8 rounded-xl shadow-xl max-w-2xl w-full">
      <!-- Logo -->
      <img src="/img/logo.png" alt="Mi Logo" class="w-48 mb-6 mx-auto rounded-lg shadow-lg" />

      <!-- Título -->
      <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">
        Bienvenido a Licores la 79
      </h1>

      <!-- Descripción -->
      <p class="text-gray-600 text-lg mb-6">Sistema de ventas e inventario</p>

      <!-- Botones -->
      <div class="flex justify-center gap-4 flex-wrap">
        <Link
          href="/login"
          class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow transition duration-200"
        >
          Iniciar Sesión
        </Link>
        <Link
          href="/register"
          class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg shadow transition duration-200"
        >
          Registrarse
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>


