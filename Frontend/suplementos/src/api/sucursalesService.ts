import { api } from './config';
import type { Sucursal, ApiResponse } from '../types';

export const sucursalesService = {
  // Listar sucursales
  list: async (): Promise<ApiResponse<Sucursal[]>> => {
    const response = await api.get('/sucursales');
    return response.data;
  },

  // Obtener una sucursal por ID
  get: async (id: number): Promise<ApiResponse<Sucursal>> => {
    const response = await api.get(`/sucursales/${id}`);
    return response.data;
  },

  // Crear sucursal
  create: async (data: Partial<Sucursal>): Promise<ApiResponse<Sucursal>> => {
    const response = await api.post('/sucursales', data);
    return response.data;
  },

  // Actualizar sucursal
  update: async (id: number, data: Partial<Sucursal>): Promise<ApiResponse<Sucursal>> => {
    const response = await api.put(`/sucursales/${id}`, data);
    return response.data;
  },
};
