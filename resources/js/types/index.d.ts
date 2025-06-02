import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}


export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

export interface SharedData {
    flash: {
        success?: string;
        error?: string;
    };
    // Otros datos que compartas globalmente con Inertia
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
            name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;

        };
    };
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
//Cliente
export interface Cliente {
    id: number;
    tipo_identificacion: string;
    numero_identificacion: string;
    nombre: string;
    apellido: string;
    direccion: string;
    telefono: string;
    correo: string;
}
//Proveedor
export interface Proveedor {
    id: number;
    razon_social: string;
    nit: string;
    direccion: string;
    telefono: string;
    correo: string;
}
//CategoriaProducto
export interface CategoriaProducto {
    id: number;
    nombre: string;
    descripcion: string;
}
//Producto
export interface Producto {
    id: number;
    nombre: string;
    descripcion: string;
    precio_compra: number;
    precio_venta: number;
    unidad_medida: string;
    stock: number;
    stock_minimo: number;
    categoria_id: number;
}

export interface Rol {
    id: number;
    nombre: string;

}
//Compra
export interface Compra {
  id: number;
  fecha: Date;
  total: number;
  estado: 'pagado' | 'cancelada';
  proveedor_id: number;
  proveedor: {
  razon_social: string;
  };
  user_id: number;
  user: {
  name: string;
  };
}
//DetalleCompra
export interface DetalleCompra {
  id: number;
  compra: { id: number };
  producto: { nombre: string };
  cantidad: number;
  precio_unitario: number;
  subtotal: number;
}
//DetalleVenta
export interface DetalleVenta {
    id: number;
    venta_id: number;
    producto?: {
    id: number
    nombre: string
    }
    cantidad: number;
    precio_unitario: number;
    subtotal: number;
    impuesto_iva: number;
    venta: Venta;

}

//Venta
export interface Venta {
  id: number;
  venta_id: number;
  cliente: Cliente;
  user: User;
  fecha_venta: string;
  tipo_comprobante: string;
  numero_factura: string;
  total: number;
  estado: 'pagado' | 'cancelada';
}
export interface Venta {
    id: number;
    venta_id: number;
    cliente_id: number;
    user_id: number;
    fecha_venta: string;
    total: number;
    metodo_pago: 'efectivo' | 'tarjeta';
    estado: 'pendiente' | 'pagado' | 'cancelada';
    tipo_comprobante: 'factura';
    // Relaciones cargadas (asumimos que el controlador las precarga)
    cliente: { id: number; nombre: string; };
    user: { id: number; name: string; };
    venta: { id: number; }; // Solo necesitamos el ID de la venta
    factura: { id: number; numero_factura: string; }; // La factura asociada
}
export interface Venta {
  id: number
  cliente?: { nombre: string }
  user: { name: string }
  fecha_venta: string
  metodo_pago: string
  estado: string
  total: number
  tipo_comprobante: string
  detalle_ventas: {
    id: number
    producto?: {
      nombre: string
    }
    cantidad: number
    precio: number
  }[]
}
//pago
export interface Pago {
    id: number;
    venta_id: number | null;
    compra_id: number | null;
    monto: number;
    metodo_pago: 'efectivo' | 'tarjeta';
    fecha_pago: string;
    referencia_pago: string | null;
    created_at: string;
    updated_at: string;

    venta?: {
        id: number;

    };
    compra?: {
        id: number;

    };
}

