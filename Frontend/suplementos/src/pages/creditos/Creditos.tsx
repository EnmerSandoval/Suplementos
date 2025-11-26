import { useState, useEffect } from 'react';
import Card from '../../components/common/Card';
import Table from '../../components/common/Table';
import type { Credito } from '../../types';
import { creditosService } from '../../api/creditosService';

export default function Creditos() {
  const [creditos, setCreditos] = useState<Credito[]>([]);

  useEffect(() => {
    loadCreditos();
  }, []);

  const loadCreditos = async () => {
    try {
      const response = await creditosService.list();
      if (response.success) setCreditos(response.data || []);
    } catch (error) {
      console.error('Error al cargar créditos:', error);
    }
  };

  const columns = [
    { key: 'id', header: 'ID' },
    { key: 'cliente_id', header: 'Cliente ID' },
    { key: 'monto_total', header: 'Monto Total', render: (c: Credito) => `$${c.monto_total.toFixed(2)}` },
    { key: 'saldo_pendiente', header: 'Saldo Pendiente', render: (c: Credito) => `$${c.saldo_pendiente.toFixed(2)}` },
    { key: 'estado', header: 'Estado', render: (c: Credito) => (
      <span className={`px-2 py-1 rounded text-sm ${
        c.estado === 'pagado' ? 'bg-green-100 text-green-800' :
        c.estado === 'vencido' ? 'bg-red-100 text-red-800' :
        'bg-yellow-100 text-yellow-800'
      }`}>
        {c.estado}
      </span>
    )},
  ];

  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 mb-6">Créditos</h1>
      <Card>
        <Table data={creditos} columns={columns} />
      </Card>
    </div>
  );
}
