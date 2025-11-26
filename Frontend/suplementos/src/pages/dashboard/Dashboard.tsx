import { useEffect, useState } from 'react';
import Card from '../../components/common/Card';
import { Package, ShoppingCart, Users, DollarSign } from 'lucide-react';

export default function Dashboard() {
  const [stats, setStats] = useState({
    totalProductos: 0,
    totalVentas: 0,
    totalClientes: 0,
    ventasHoy: 0,
  });

  useEffect(() => {
    loadStats();
  }, []);

  const loadStats = async () => {
    try {
      // Aquí se cargarían las estadísticas reales
      // Por ahora, valores de ejemplo
      setStats({
        totalProductos: 150,
        totalVentas: 1250,
        totalClientes: 85,
        ventasHoy: 25,
      });
    } catch (error) {
      console.error('Error al cargar estadísticas:', error);
    }
  };

  const statCards = [
    { title: 'Total Productos', value: stats.totalProductos, icon: Package, color: 'bg-blue-500' },
    { title: 'Total Ventas', value: `$${stats.totalVentas}`, icon: DollarSign, color: 'bg-green-500' },
    { title: 'Clientes', value: stats.totalClientes, icon: Users, color: 'bg-purple-500' },
    { title: 'Ventas Hoy', value: stats.ventasHoy, icon: ShoppingCart, color: 'bg-orange-500' },
  ];

  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 mb-6">Dashboard</h1>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {statCards.map((stat, index) => (
          <Card key={index}>
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-600">{stat.title}</p>
                <p className="text-2xl font-bold text-gray-900 mt-1">{stat.value}</p>
              </div>
              <div className={`${stat.color} p-3 rounded-lg`}>
                <stat.icon className="w-6 h-6 text-white" />
              </div>
            </div>
          </Card>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card title="Actividad Reciente">
          <p className="text-gray-600">No hay actividad reciente</p>
        </Card>

        <Card title="Productos por Vencer">
          <p className="text-gray-600">No hay productos próximos a vencer</p>
        </Card>
      </div>
    </div>
  );
}
