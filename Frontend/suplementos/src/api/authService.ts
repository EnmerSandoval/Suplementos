import { api } from './config';
import type { LoginCredentials, AuthResponse, User } from '../types';

export const authService = {
  // Iniciar sesión
  login: async (credentials: LoginCredentials): Promise<AuthResponse> => {
    const response = await api.post<AuthResponse>('/auth/login', credentials);
    return response.data;
  },

  // Obtener información del usuario actual
  me: async (): Promise<{ success: boolean; data: User }> => {
    const response = await api.get('/auth/me');
    return response.data;
  },

  // Cerrar sesión
  logout: async (): Promise<void> => {
    await api.post('/auth/logout');
  },

  // Cambiar contraseña
  changePassword: async (data: { current_password: string; new_password: string }): Promise<void> => {
    await api.post('/auth/change-password', data);
  },
};
