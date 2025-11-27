// Tipos de autenticación
export interface User {
  id: number;
  nombre: string;
  email: string;
  rol: 'administrador' | 'vendedor';
  sucursal_id: number;
  activo: boolean;
}

export interface LoginCredentials {
  usuario: string;
  contrasena: string;
}

export interface AuthResponse {
  success: boolean;
  data: {
    user: User;
    token: string;
  };
}

// Tipos de productos
export interface Producto {
  id: number;
  codigo: string;
  nombre: string;
  descripcion?: string;
  precio_venta: number;
  precio_compra: number;
  categoria: string;
  unidad_medida: string;
  requiere_lote: boolean;
  requiere_fecha_vencimiento: boolean;
  activo: boolean;
  created_at: string;
  updated_at: string;
}

// Tipos de inventario
export interface Inventario {
  id: number;
  producto_id: number;
  sucursal_id: number;
  lote?: string;
  fecha_vencimiento?: string;
  cantidad: number;
  precio_costo: number;
  producto?: Producto;
}

export interface MovimientoInventario {
  id: number;
  tipo: 'entrada' | 'salida' | 'traslado' | 'ajuste';
  producto_id: number;
  sucursal_origen_id?: number;
  sucursal_destino_id?: number;
  lote?: string;
  fecha_vencimiento?: string;
  cantidad: number;
  precio_costo?: number;
  motivo?: string;
  usuario_id: number;
  created_at: string;
}

// Tipos de clientes
export interface Cliente {
  id: number;
  nombre: string;
  email?: string;
  telefono?: string;
  direccion?: string;
  limite_credito: number;
  activo: boolean;
  created_at: string;
  updated_at: string;
}

// Tipos de ventas
export interface DetalleVenta {
  producto_id: number;
  lote?: string;
  cantidad: number;
  precio_unitario: number;
  descuento: number;
  subtotal: number;
}

export interface Venta {
  id: number;
  sucursal_id: number;
  usuario_id: number;
  cliente_id?: number;
  tipo_pago: 'efectivo' | 'tarjeta' | 'credito' | 'mixto';
  subtotal: number;
  descuento: number;
  total: number;
  estado: 'completada' | 'cancelada';
  detalles: DetalleVenta[];
  created_at: string;
}

// Tipos de créditos
export interface Credito {
  id: number;
  venta_id: number;
  cliente_id: number;
  monto_total: number;
  monto_pagado: number;
  saldo_pendiente: number;
  fecha_vencimiento: string;
  estado: 'pendiente' | 'pagado' | 'vencido';
  created_at: string;
}

export interface PagoCredito {
  id: number;
  credito_id: number;
  monto: number;
  metodo_pago: 'efectivo' | 'tarjeta';
  usuario_id: number;
  created_at: string;
}

// Tipos de cotizaciones
export interface Cotizacion {
  id: number;
  sucursal_id: number;
  usuario_id: number;
  cliente_id?: number;
  subtotal: number;
  descuento: number;
  total: number;
  vigencia_hasta: string;
  estado: 'vigente' | 'expirada' | 'convertida';
  detalles: DetalleVenta[];
  created_at: string;
  updated_at: string;
}

// Tipos de sucursales
export interface Sucursal {
  id: number;
  codigo: string;
  nombre: string;
  direccion?: string;
  telefono?: string;
  activo: boolean;
  created_at: string;
  updated_at: string;
}

// Tipos de cierres de caja
export interface CierreCaja {
  id: number;
  sucursal_id: number;
  usuario_apertura_id: number;
  usuario_cierre_id?: number;
  monto_inicial: number;
  monto_efectivo_esperado: number;
  monto_efectivo_real?: number;
  monto_tarjeta: number;
  diferencia?: number;
  estado: 'abierto' | 'cerrado';
  fecha_apertura: string;
  fecha_cierre?: string;
}

// Tipos de reportes
export interface ReporteInventario {
  producto_id: number;
  codigo: string;
  nombre: string;
  sucursal: string;
  cantidad: number;
  valor_total: number;
}

export interface ReporteVentas {
  fecha: string;
  sucursal: string;
  total_ventas: number;
  cantidad_ventas: number;
  tipo_pago: string;
}

export interface ProductoPorVencer {
  producto_id: number;
  codigo: string;
  nombre: string;
  lote: string;
  fecha_vencimiento: string;
  dias_para_vencer: number;
  cantidad: number;
  sucursal: string;
}

// Tipo de respuesta API genérica
export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  message?: string;
  error?: string;
}
