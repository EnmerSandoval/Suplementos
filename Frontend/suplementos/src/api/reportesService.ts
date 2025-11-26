import { api } from './config';
import type { ReporteInventario, ReporteVentas, ProductoPorVencer, ApiResponse } from '../types';

export const reportesService = {
  // Reporte de inventario por sucursal
  inventarioPorSucursal: async (params?: { sucursal_id?: number }): Promise<ApiResponse<ReporteInventario[]>> => {
    const response = await api.get('/reportes/inventario', { params });
    return response.data;
  },

  // Reporte de productos por vencer
  productosPorVencer: async (params?: { dias?: number }): Promise<ApiResponse<ProductoPorVencer[]>> => {
    const response = await api.get('/reportes/productos-por-vencer', { params });
    return response.data;
  },

  // Reporte de ventas por sucursal
  ventasPorSucursal: async (params?: { fecha_desde?: string; fecha_hasta?: string; sucursal_id?: number }): Promise<ApiResponse<ReporteVentas[]>> => {
    const response = await api.get('/reportes/ventas', { params });
    return response.data;
  },

  // Reporte de cuadre de caja
  cuadreCaja: async (params?: { fecha_desde?: string; fecha_hasta?: string; sucursal_id?: number }): Promise<ApiResponse<any>> => {
    const response = await api.get('/reportes/cuadre-caja', { params });
    return response.data;
  },

  // Reporte de convenios mensuales
  conveniosMensuales: async (params?: { mes?: string; sucursal_id?: number }): Promise<ApiResponse<any>> => {
    const response = await api.get('/reportes/convenios-mensuales', { params });
    return response.data;
  },

  // Reporte de créditos por cliente
  creditosPorCliente: async (params?: { cliente_id?: number }): Promise<ApiResponse<any>> => {
    const response = await api.get('/reportes/creditos-cliente', { params });
    return response.data;
  },
};
