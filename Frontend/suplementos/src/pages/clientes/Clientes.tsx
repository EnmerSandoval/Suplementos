import { useState, useEffect } from 'react';
import { Plus } from 'lucide-react';
import Card from '../../components/common/Card';
import Table from '../../components/common/Table';
import Button from '../../components/common/Button';
import Modal from '../../components/common/Modal';
import Input from '../../components/common/Input';
import type { Cliente } from '../../types';
import { clientesService } from '../../api/clientesService';

export default function Clientes() {
  const [clientes, setClientes] = useState<Cliente[]>([]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [formData, setFormData] = useState<Partial<Cliente>>({});

  useEffect(() => {
    loadClientes();
  }, []);

  const loadClientes = async () => {
    try {
      const response = await clientesService.list();
      if (response.success) setClientes(response.data || []);
    } catch (error) {
      console.error('Error al cargar clientes:', error);
    }
  };

  const handleSubmit = async () => {
    try {
      const response = await clientesService.create(formData);
      if (response.success) {
        await loadClientes();
        setIsModalOpen(false);
        setFormData({});
      }
    } catch (error) {
      console.error('Error al crear cliente:', error);
    }
  };

  const columns = [
    { key: 'nombre', header: 'Nombre' },
    { key: 'email', header: 'Email' },
    { key: 'telefono', header: 'Teléfono' },
    { key: 'limite_credito', header: 'Límite Crédito', render: (c: Cliente) => `$${c.limite_credito.toFixed(2)}` },
  ];

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-900">Clientes</h1>
        <Button variant="primary" onClick={() => setIsModalOpen(true)}>
          <Plus className="w-5 h-5 mr-2 inline" />
          Nuevo Cliente
        </Button>
      </div>

      <Card>
        <Table data={clientes} columns={columns} />
      </Card>

      <Modal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} title="Nuevo Cliente">
        <Input label="Nombre" value={formData.nombre || ''} onChange={(e) => setFormData({ ...formData, nombre: e.target.value })} />
        <Input label="Email" type="email" value={formData.email || ''} onChange={(e) => setFormData({ ...formData, email: e.target.value })} />
        <Input label="Teléfono" value={formData.telefono || ''} onChange={(e) => setFormData({ ...formData, telefono: e.target.value })} />
        <Button variant="primary" onClick={handleSubmit} className="w-full mt-4">Guardar</Button>
      </Modal>
    </div>
  );
}
