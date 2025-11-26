import { useState, useEffect } from 'react';
import { Plus } from 'lucide-react';
import Card from '../../components/common/Card';
import Table from '../../components/common/Table';
import Button from '../../components/common/Button';
import type { CierreCaja } from '../../types';
import { cierresCajaService } from '../../api/cierresCajaService';

export default function CierresCaja() {
  const [cierres, setCierres] = useState<CierreCaja[]>([]);

  useEffect(() => {
    loadCierres();
  }, []);

  const loadCierres = async () => {
    try {
      const response = await cierresCajaService.list();
      if (response.success) setCierres(response.data || []);
    } catch (error) {
      console.error('Error al cargar cierres:', error);
    }
  };

  const columns = [
    { key: 'id', header: 'ID' },
    { key: 'fecha_apertura', header: 'Apertura' },
    { key: 'monto_inicial', header: 'Monto Inicial', render: (c: CierreCaja) => `$${c.monto_inicial.toFixed(2)}` },
    { key: 'estado', header: 'Estado', render: (c: CierreCaja) => (
      <span className={`px-2 py-1 rounded text-sm ${c.estado === 'cerrado' ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800'}`}>
        {c.estado}
      </span>
    )},
  ];

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-900">Cierres de Caja</h1>
        <Button variant="primary">
          <Plus className="w-5 h-5 mr-2 inline" />
          Nuevo Cierre
        </Button>
      </div>
      <Card>
        <Table data={cierres} columns={columns} />
      </Card>
    </div>
  );
}
