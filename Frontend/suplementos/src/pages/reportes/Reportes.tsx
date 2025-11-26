import Card from '../../components/common/Card';
import Button from '../../components/common/Button';
import { BarChart3, Package, TrendingUp, DollarSign } from 'lucide-react';

export default function Reportes() {
  const reportes = [
    { title: 'Inventario por Sucursal', icon: Package, description: 'Ver inventario de cada sucursal' },
    { title: 'Productos por Vencer', icon: Package, description: 'Productos próximos a vencer' },
    { title: 'Ventas por Sucursal', icon: TrendingUp, description: 'Reporte de ventas' },
    { title: 'Cuadre de Caja', icon: DollarSign, description: 'Cuadre de cajas' },
    { title: 'Convenios Mensuales', icon: BarChart3, description: 'Reporte de convenios' },
    { title: 'Créditos por Cliente', icon: DollarSign, description: 'Estado de créditos' },
  ];

  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 mb-6">Reportes</h1>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {reportes.map((reporte, index) => (
          <Card key={index}>
            <div className="flex items-start gap-4">
              <div className="bg-blue-100 p-3 rounded-lg">
                <reporte.icon className="w-6 h-6 text-blue-600" />
              </div>
              <div className="flex-1">
                <h3 className="font-semibold text-gray-900 mb-1">{reporte.title}</h3>
                <p className="text-sm text-gray-600 mb-3">{reporte.description}</p>
                <Button variant="primary" size="sm">
                  Generar Reporte
                </Button>
              </div>
            </div>
          </Card>
        ))}
      </div>
    </div>
  );
}
