import { api } from './config';
import type { Producto, ApiResponse } from '../types';

export const productosService = {
  // Listar productos
  list: async (params?: { search?: string; categoria?: string }): Promise<ApiResponse<Producto[]>> => {
    const response = await api.get('/productos', { params });
    return response.data;
  },

  // Obtener un producto por ID
  get: async (id: number): Promise<ApiResponse<Producto>> => {
    const response = await api.get(`/productos/${id}`);
    return response.data;
  },

  // Crear producto
  create: async (data: Partial<Producto>): Promise<ApiResponse<Producto>> => {
    const response = await api.post('/productos', data);
    return response.data;
  },

  // Actualizar producto
  update: async (id: number, data: Partial<Producto>): Promise<ApiResponse<Producto>> => {
    const response = await api.put(`/productos/${id}`, data);
    return response.data;
  },
};
