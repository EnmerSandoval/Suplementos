import { useState, useEffect } from 'react';
import { Plus } from 'lucide-react';
import Card from '../../components/common/Card';
import Table from '../../components/common/Table';
import Button from '../../components/common/Button';
import Modal from '../../components/common/Modal';
import Input from '../../components/common/Input';
import type { Producto } from '../../types';
import { productosService } from '../../api/productosService';

export default function Productos() {
  const [productos, setProductos] = useState<Producto[]>([]);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [formData, setFormData] = useState<Partial<Producto>>({});

  useEffect(() => {
    loadProductos();
  }, []);

  const loadProductos = async () => {
    try {
      const response = await productosService.list();
      if (response.success) setProductos(response.data || []);
    } catch (error) {
      console.error('Error al cargar productos:', error);
    }
  };

  const handleSubmit = async () => {
    try {
      const response = await productosService.create(formData);
      if (response.success) {
        await loadProductos();
        setIsModalOpen(false);
        setFormData({});
      }
    } catch (error) {
      console.error('Error al crear producto:', error);
    }
  };

  const columns = [
    { key: 'codigo', header: 'Código' },
    { key: 'nombre', header: 'Nombre' },
    { key: 'categoria', header: 'Categoría' },
    { key: 'precio_venta', header: 'Precio', render: (p: Producto) => `$${p.precio_venta.toFixed(2)}` },
    { key: 'activo', header: 'Estado', render: (p: Producto) => (
      <span className={`px-2 py-1 rounded text-sm ${p.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
        {p.activo ? 'Activo' : 'Inactivo'}
      </span>
    )},
  ];

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-900">Productos</h1>
        <Button variant="primary" onClick={() => setIsModalOpen(true)}>
          <Plus className="w-5 h-5 mr-2 inline" />
          Nuevo Producto
        </Button>
      </div>

      <Card>
        <Table data={productos} columns={columns} />
      </Card>

      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title="Nuevo Producto"
      >
        <Input
          label="Código"
          value={formData.codigo || ''}
          onChange={(e) => setFormData({ ...formData, codigo: e.target.value })}
        />
        <Input
          label="Nombre"
          value={formData.nombre || ''}
          onChange={(e) => setFormData({ ...formData, nombre: e.target.value })}
        />
        <Input
          label="Categoría"
          value={formData.categoria || ''}
          onChange={(e) => setFormData({ ...formData, categoria: e.target.value })}
        />
        <Input
          label="Precio de Venta"
          type="number"
          step="0.01"
          value={formData.precio_venta || ''}
          onChange={(e) => setFormData({ ...formData, precio_venta: parseFloat(e.target.value) })}
        />
        <Button variant="primary" onClick={handleSubmit} className="w-full mt-4">
          Guardar
        </Button>
      </Modal>
    </div>
  );
}
