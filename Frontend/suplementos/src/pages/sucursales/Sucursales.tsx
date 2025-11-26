import { useState, useEffect } from 'react';
import { Plus } from 'lucide-react';
import Card from '../../components/common/Card';
import Table from '../../components/common/Table';
import Button from '../../components/common/Button';
import type { Sucursal } from '../../types';
import { sucursalesService } from '../../api/sucursalesService';

export default function Sucursales() {
  const [sucursales, setSucursales] = useState<Sucursal[]>([]);

  useEffect(() => {
    loadSucursales();
  }, []);

  const loadSucursales = async () => {
    try {
      const response = await sucursalesService.list();
      if (response.success) setSucursales(response.data || []);
    } catch (error) {
      console.error('Error al cargar sucursales:', error);
    }
  };

  const columns = [
    { key: 'codigo', header: 'Código' },
    { key: 'nombre', header: 'Nombre' },
    { key: 'direccion', header: 'Dirección' },
    { key: 'telefono', header: 'Teléfono' },
    { key: 'activo', header: 'Estado', render: (s: Sucursal) => (
      <span className={`px-2 py-1 rounded text-sm ${s.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
        {s.activo ? 'Activo' : 'Inactivo'}
      </span>
    )},
  ];

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-900">Sucursales</h1>
        <Button variant="primary">
          <Plus className="w-5 h-5 mr-2 inline" />
          Nueva Sucursal
        </Button>
      </div>
      <Card>
        <Table data={sucursales} columns={columns} />
      </Card>
    </div>
  );
}
