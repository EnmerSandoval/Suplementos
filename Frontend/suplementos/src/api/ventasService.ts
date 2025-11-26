import { api } from './config';
import type { Venta, ApiResponse } from '../types';

export const ventasService = {
  // Listar ventas
  list: async (params?: { fecha_desde?: string; fecha_hasta?: string; sucursal_id?: number }): Promise<ApiResponse<Venta[]>> => {
    const response = await api.get('/ventas', { params });
    return response.data;
  },

  // Obtener una venta por ID
  get: async (id: number): Promise<ApiResponse<Venta>> => {
    const response = await api.get(`/ventas/${id}`);
    return response.data;
  },

  // Crear venta
  create: async (data: Partial<Venta>): Promise<ApiResponse<Venta>> => {
    const response = await api.post('/ventas', data);
    return response.data;
  },
};
