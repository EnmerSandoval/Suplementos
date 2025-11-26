import { api } from './config';
import type { Credito, PagoCredito, ApiResponse } from '../types';

export const creditosService = {
  // Listar créditos
  list: async (params?: { estado?: string; cliente_id?: number }): Promise<ApiResponse<Credito[]>> => {
    const response = await api.get('/creditos', { params });
    return response.data;
  },

  // Obtener un crédito por ID
  get: async (id: number): Promise<ApiResponse<Credito>> => {
    const response = await api.get(`/creditos/${id}`);
    return response.data;
  },

  // Obtener créditos de un cliente
  getByCliente: async (clienteId: number): Promise<ApiResponse<Credito[]>> => {
    const response = await api.get(`/creditos/cliente/${clienteId}`);
    return response.data;
  },

  // Registrar pago de crédito
  registrarPago: async (id: number, data: Partial<PagoCredito>): Promise<ApiResponse> => {
    const response = await api.post(`/creditos/${id}/pagar`, data);
    return response.data;
  },
};
