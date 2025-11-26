import { api } from './config';
import type { Inventario, MovimientoInventario, ApiResponse } from '../types';

export const inventarioService = {
  // Listar inventario
  list: async (params?: { sucursal_id?: number; producto_id?: number }): Promise<ApiResponse<Inventario[]>> => {
    const response = await api.get('/inventario', { params });
    return response.data;
  },

  // Obtener inventario por sucursal
  getBySucursal: async (sucursalId: number): Promise<ApiResponse<Inventario[]>> => {
    const response = await api.get(`/inventario/sucursal/${sucursalId}`);
    return response.data;
  },

  // Registrar entrada de inventario
  entrada: async (data: Partial<MovimientoInventario>): Promise<ApiResponse> => {
    const response = await api.post('/inventario/entrada', data);
    return response.data;
  },

  // Registrar traslado de inventario
  traslado: async (data: Partial<MovimientoInventario>): Promise<ApiResponse> => {
    const response = await api.post('/inventario/traslado', data);
    return response.data;
  },

  // Registrar ajuste de inventario
  ajuste: async (data: Partial<MovimientoInventario>): Promise<ApiResponse> => {
    const response = await api.post('/inventario/ajuste', data);
    return response.data;
  },
};
