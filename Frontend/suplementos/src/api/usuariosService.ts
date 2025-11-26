import { api } from './config';
import type { User, ApiResponse } from '../types';

export const usuariosService = {
  // Listar usuarios
  list: async (): Promise<ApiResponse<User[]>> => {
    const response = await api.get('/usuarios');
    return response.data;
  },

  // Obtener un usuario por ID
  get: async (id: number): Promise<ApiResponse<User>> => {
    const response = await api.get(`/usuarios/${id}`);
    return response.data;
  },

  // Crear usuario
  create: async (data: Partial<User> & { password: string }): Promise<ApiResponse<User>> => {
    const response = await api.post('/usuarios', data);
    return response.data;
  },

  // Actualizar usuario
  update: async (id: number, data: Partial<User>): Promise<ApiResponse<User>> => {
    const response = await api.put(`/usuarios/${id}`, data);
    return response.data;
  },

  // Eliminar usuario
  delete: async (id: number): Promise<ApiResponse> => {
    const response = await api.delete(`/usuarios/${id}`);
    return response.data;
  },
};
