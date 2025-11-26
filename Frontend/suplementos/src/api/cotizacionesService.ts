import { api } from './config';
import type { Cotizacion, ApiResponse } from '../types';

export const cotizacionesService = {
  // Listar cotizaciones
  list: async (params?: { estado?: string }): Promise<ApiResponse<Cotizacion[]>> => {
    const response = await api.get('/cotizaciones', { params });
    return response.data;
  },

  // Obtener una cotización por ID
  get: async (id: number): Promise<ApiResponse<Cotizacion>> => {
    const response = await api.get(`/cotizaciones/${id}`);
    return response.data;
  },

  // Crear cotización
  create: async (data: Partial<Cotizacion>): Promise<ApiResponse<Cotizacion>> => {
    const response = await api.post('/cotizaciones', data);
    return response.data;
  },

  // Actualizar cotización
  update: async (id: number, data: Partial<Cotizacion>): Promise<ApiResponse<Cotizacion>> => {
    const response = await api.put(`/cotizaciones/${id}`, data);
    return response.data;
  },

  // Convertir cotización a venta
  convertirAVenta: async (id: number): Promise<ApiResponse> => {
    const response = await api.post(`/cotizaciones/${id}/convertir`);
    return response.data;
  },
};
