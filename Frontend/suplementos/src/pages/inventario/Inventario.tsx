import { useState, useEffect } from 'react';
import Card from '../../components/common/Card';
import Table from '../../components/common/Table';
import type { Inventario } from '../../types';
import { inventarioService } from '../../api/inventarioService';
import { useAuth } from '../../context/AuthContext';

export default function InventarioPage() {
  const { user } = useAuth();
  const [inventario, setInventario] = useState<Inventario[]>([]);

  useEffect(() => {
    loadInventario();
  }, []);

  const loadInventario = async () => {
    try {
      const response = await inventarioService.getBySucursal(user?.sucursal_id || 1);
      if (response.success) setInventario(response.data || []);
    } catch (error) {
      console.error('Error al cargar inventario:', error);
    }
  };

  const columns = [
    { key: 'producto_id', header: 'Producto ID' },
    { key: 'cantidad', header: 'Cantidad' },
    { key: 'lote', header: 'Lote' },
    { key: 'fecha_vencimiento', header: 'Vencimiento' },
    { key: 'precio_costo', header: 'Costo', render: (i: Inventario) => `$${i.precio_costo.toFixed(2)}` },
  ];

  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 mb-6">Inventario</h1>
      <Card>
        <Table data={inventario} columns={columns} />
      </Card>
    </div>
  );
}
