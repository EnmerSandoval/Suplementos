import { useState, useEffect } from 'react';
import { Plus } from 'lucide-react';
import Card from '../../components/common/Card';
import Table from '../../components/common/Table';
import Button from '../../components/common/Button';
import type { User } from '../../types';
import { usuariosService } from '../../api/usuariosService';

export default function Usuarios() {
  const [usuarios, setUsuarios] = useState<User[]>([]);

  useEffect(() => {
    loadUsuarios();
  }, []);

  const loadUsuarios = async () => {
    try {
      const response = await usuariosService.list();
      if (response.success) setUsuarios(response.data || []);
    } catch (error) {
      console.error('Error al cargar usuarios:', error);
    }
  };

  const columns = [
    { key: 'nombre', header: 'Nombre' },
    { key: 'email', header: 'Email' },
    { key: 'rol', header: 'Rol' },
    { key: 'activo', header: 'Estado', render: (u: User) => (
      <span className={`px-2 py-1 rounded text-sm ${u.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
        {u.activo ? 'Activo' : 'Inactivo'}
      </span>
    )},
  ];

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-900">Usuarios</h1>
        <Button variant="primary">
          <Plus className="w-5 h-5 mr-2 inline" />
          Nuevo Usuario
        </Button>
      </div>
      <Card>
        <Table data={usuarios} columns={columns} />
      </Card>
    </div>
  );
}
