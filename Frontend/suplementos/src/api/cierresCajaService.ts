import { api } from './config';
import type { CierreCaja, ApiResponse } from '../types';

export const cierresCajaService = {
  // Listar cierres de caja
  list: async (params?: { sucursal_id?: number; estado?: string }): Promise<ApiResponse<CierreCaja[]>> => {
    const response = await api.get('/cierres-caja', { params });
    return response.data;
  },

  // Obtener un cierre de caja por ID
  get: async (id: number): Promise<ApiResponse<CierreCaja>> => {
    const response = await api.get(`/cierres-caja/${id}`);
    return response.data;
  },

  // Crear cierre de caja
  crear: async (data: { sucursal_id: number; monto_inicial: number }): Promise<ApiResponse<CierreCaja>> => {
    const response = await api.post('/cierres-caja', data);
    return response.data;
  },

  // Cerrar caja
  cerrar: async (id: number, data: { monto_efectivo_real: number }): Promise<ApiResponse> => {
    const response = await api.put(`/cierres-caja/${id}/cerrar`, data);
    return response.data;
  },
};
