import { api } from './config';
import type { Cliente, ApiResponse } from '../types';

export const clientesService = {
  // Listar clientes
  list: async (params?: { search?: string }): Promise<ApiResponse<Cliente[]>> => {
    const response = await api.get('/clientes', { params });
    return response.data;
  },

  // Obtener un cliente por ID
  get: async (id: number): Promise<ApiResponse<Cliente>> => {
    const response = await api.get(`/clientes/${id}`);
    return response.data;
  },

  // Crear cliente
  create: async (data: Partial<Cliente>): Promise<ApiResponse<Cliente>> => {
    const response = await api.post('/clientes', data);
    return response.data;
  },

  // Actualizar cliente
  update: async (id: number, data: Partial<Cliente>): Promise<ApiResponse<Cliente>> => {
    const response = await api.put(`/clientes/${id}`, data);
    return response.data;
  },
};
