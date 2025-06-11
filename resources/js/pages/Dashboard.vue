<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { computed, onMounted } from 'vue';

// Importar los componentes de Chart.js para Vue
import { Bar, Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement
} from 'chart.js';

// Registrar los componentes y elementos necesarios de Chart.js
ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement
);

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
];

interface Metrics {
  stock_bajo: number;
  ventas_recientes: { id: number; cliente_nombre: string; total: number; fecha_venta: string }[];
  total_ventas: number;
  ventas_diarias: Record<string, number>;
  compras_recientes: { id: number; proveedor_razon_social: string; total: number; fecha_compra: string }[];
  total_compras: number;
  reportes_por_tipo: Record<string, number>;
  total_reportes: number;
}

interface Props {
  metrics: Metrics;
}

const props = defineProps<Props>();

// Función modificada para mostrar sin decimales y con puntos de mil
const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
    useGrouping: true,
  }).format(value);
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('es-CO', { year: 'numeric', month: 'short', day: '2-digit' });
};


// Gráfico de ventas diarias
const ventasDiariasChartData = computed(() => {
  const labels = [];
  const data = [];
  const startDate = new Date();
  startDate.setDate(startDate.getDate() - 6);

  for (let i = 0; i < 7; i++) {
    const date = new Date(startDate);
    date.setDate(startDate.getDate() + i);
    const dateStr = date.toISOString().split('T')[0];
    labels.push(date.toLocaleDateString('es-CO', { weekday: 'short', day: 'numeric' }));
    data.push(props.metrics.ventas_diarias[dateStr] || 0);
  }

  return {
    labels,
    datasets: [{
      label: 'Ventas Diarias',
      data,
      backgroundColor: 'rgba(34, 197, 94, 0.5)', // Verde claro
      borderColor: 'rgba(34, 197, 94, 1)', // Verde
      borderWidth: 1,
    }],
  };
});

// Opciones para el gráfico de ventas diarias
const ventasDiariasChartOptions = {
  responsive: true,
  maintainAspectRatio: false, // Permitir que el gráfico no mantenga su aspecto ratio por defecto
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: function(value: string | number) {
          return formatCurrency(Number(value)); // Formatear los ticks del eje Y como moneda
        }
      }
    },
    x: {
        grid: {
            display: false // Ocultar líneas de la cuadrícula en el eje X
        }
    }
  },
  plugins: {
    tooltip: {
      callbacks: {
        label: function(context: any) {
          return `${context.dataset.label}: ${formatCurrency(context.parsed.y)}`;
        }
      }
    }
  }
};


// Gráfico de reportes por tipo
const reportesPorTipoChartData = computed(() => {
  const labels = ['Inventario', 'Ventas', 'Compras'];
  const data = [
    props.metrics.reportes_por_tipo.inventario || 0,
    props.metrics.reportes_por_tipo.ventas || 0,
    props.metrics.reportes_por_tipo.compras || 0,
  ];

  return {
    labels,
    datasets: [{
      data,
      backgroundColor: [
        'rgba(59, 130, 246, 0.7)', // Azul más opaco
        'rgba(34, 197, 94, 0.7)',  // Verde más opaco
        'rgba(239, 68, 68, 0.7)',  // Rojo más opaco
      ],
      borderColor: [
        'rgba(59, 130, 246, 1)',
        'rgba(34, 197, 94, 1)',
        'rgba(239, 68, 68, 1)',
      ],
      borderWidth: 1,
    }],
  };
});

// Opciones para el gráfico de reportes por tipo
const reportesPorTipoChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    tooltip: {
      callbacks: {
        label: function(context: any) {
          const total = context.dataset.data.reduce((acc: number, val: number) => acc + val, 0);
          const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(2) : 0;
          return `${context.label}: ${context.parsed} (${percentage}%)`;
        }
      }
    }
  }
};

</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Tarjetas de Métricas -->
      <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <Card class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <CardHeader>
            <CardTitle class="text-lg font-semibold">Stock Bajo</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold text-red-600">{{ metrics.stock_bajo }} productos</p>
            <Button as-child variant="link" class="mt-2">
              <Link href="/inventarios">Ver Inventario</Link>
            </Button>
          </CardContent>
        </Card>

        <Card class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <CardHeader>
            <CardTitle class="text-lg font-semibold">Ventas (7 días)</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(metrics.total_ventas) }}</p>
            <Button as-child variant="link" class="mt-2">
              <Link href="/ventas">Ver Ventas</Link>
            </Button>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>ID</TableHead>
                  <TableHead>Cliente</TableHead>
                  <TableHead>Total</TableHead>
                  <TableHead>Fecha</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="!metrics.ventas_recientes.length">
                  <TableCell colspan="4" class="text-center">No hay ventas recientes.</TableCell>
                </TableRow>
                <TableRow v-for="venta in metrics.ventas_recientes" :key="venta.id">
                  <TableCell>{{ venta.id }}</TableCell>
                  <TableCell>{{ venta.cliente_nombre }}</TableCell>
                  <TableCell>{{ formatCurrency(venta.total) }}</TableCell>
                  <TableCell>{{ formatDate(venta.fecha_venta) }}</TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </CardContent>
        </Card>

        <Card class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <CardHeader>
            <CardTitle class="text-lg font-semibold">Compras (7 días)</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(metrics.total_compras) }}</p>
            <Button as-child variant="link" class="mt-2">
              <Link href="/compras">Ver Compras</Link>
            </Button>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>ID</TableHead>
                  <TableHead>Proveedor</TableHead>
                  <TableHead>Total</TableHead>
                  <TableHead>Fecha</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="!metrics.compras_recientes.length">
                  <TableCell colspan="4" class="text-center">No hay compras recientes.</TableCell>
                </TableRow>
                <TableRow v-for="compra in metrics.compras_recientes" :key="compra.id">
                  <TableCell>{{ compra.id }}</TableCell>
                  <TableCell>{{ compra.proveedor_razon_social }}</TableCell>
                  <TableCell>{{ formatCurrency(compra.total) }}</TableCell>
                  <TableCell>{{ formatDate(compra.fecha_compra) }}</TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </div>

      <!-- Sección de Gráficos -->
      <div class="grid auto-rows-min gap-4 md:grid-cols-2">
        <!-- Gráfico de Ventas Diarias -->
        <Card class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <CardHeader>
            <CardTitle class="text-lg font-semibold">Ventas Diarias (Últimos 7 días)</CardTitle>
          </CardHeader>
          <CardContent class="h-80 flex items-center justify-center">
            <div v-if="ventasDiariasChartData.datasets[0].data.some(d => d > 0)" class="w-full h-full">
                <Bar
                    :data="ventasDiariasChartData"
                    :options="ventasDiariasChartOptions"
                />
            </div>
            <p v-else class="text-gray-500">No hay datos de ventas para mostrar el gráfico.</p>
          </CardContent>
        </Card>

        <!-- Gráfico de Reportes por Tipo -->
        <Card class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <CardHeader>
            <CardTitle class="text-lg font-semibold">Reportes por Tipo (Último Mes)</CardTitle>
          </CardHeader>
          <CardContent class="h-80 flex items-center justify-center">
            <div v-if="reportesPorTipoChartData.datasets[0].data.some(d => d > 0)" class="w-full h-full">
                <Doughnut
                    :data="reportesPorTipoChartData"
                    :options="reportesPorTipoChartOptions"
                />
            </div>
            <p v-else class="text-gray-500">No hay datos de reportes para mostrar el gráfico.</p>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
