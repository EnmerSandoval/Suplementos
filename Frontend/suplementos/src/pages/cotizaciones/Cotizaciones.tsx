import { useState, useEffect } from 'react';
import { Plus } from 'lucide-react';
import Card from '../../components/common/Card';
import Table from '../../components/common/Table';
import Button from '../../components/common/Button';
import type { Cotizacion } from '../../types';
import { cotizacionesService } from '../../api/cotizacionesService';

export default function Cotizaciones() {
  const [cotizaciones, setCotizaciones] = useState<Cotizacion[]>([]);

  useEffect(() => {
    loadCotizaciones();
  }, []);

  const loadCotizaciones = async () => {
    try {
      const response = await cotizacionesService.list();
      if (response.success) setCotizaciones(response.data || []);
    } catch (error) {
      console.error('Error al cargar cotizaciones:', error);
    }
  };

  const columns = [
    { key: 'id', header: 'ID' },
    { key: 'total', header: 'Total', render: (c: Cotizacion) => `$${c.total.toFixed(2)}` },
    { key: 'vigencia_hasta', header: 'Vigencia' },
    { key: 'estado', header: 'Estado' },
  ];

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-900">Cotizaciones</h1>
        <Button variant="primary">
          <Plus className="w-5 h-5 mr-2 inline" />
          Nueva Cotización
        </Button>
      </div>
      <Card>
        <Table data={cotizaciones} columns={columns} />
      </Card>
    </div>
  );
}
