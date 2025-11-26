import { Link, useLocation } from 'react-router-dom';
import {
  LayoutDashboard,
  ShoppingCart,
  Package,
  Warehouse,
  Users,
  CreditCard,
  FileText,
  MapPin,
  UserCog,
  DollarSign,
  BarChart3,
} from 'lucide-react';
import { useAuth } from '../../context/AuthContext';

interface MenuItem {
  path: string;
  label: string;
  icon: React.ReactNode;
  adminOnly?: boolean;
}

export default function Sidebar() {
  const location = useLocation();
  const { user } = useAuth();

  const menuItems: MenuItem[] = [
    { path: '/dashboard', label: 'Dashboard', icon: <LayoutDashboard className="w-5 h-5" /> },
    { path: '/ventas', label: 'Ventas (POS)', icon: <ShoppingCart className="w-5 h-5" /> },
    { path: '/productos', label: 'Productos', icon: <Package className="w-5 h-5" /> },
    { path: '/inventario', label: 'Inventario', icon: <Warehouse className="w-5 h-5" /> },
    { path: '/clientes', label: 'Clientes', icon: <Users className="w-5 h-5" /> },
    { path: '/creditos', label: 'Créditos', icon: <CreditCard className="w-5 h-5" /> },
    { path: '/cotizaciones', label: 'Cotizaciones', icon: <FileText className="w-5 h-5" /> },
    { path: '/cierres-caja', label: 'Cierres de Caja', icon: <DollarSign className="w-5 h-5" /> },
    { path: '/reportes', label: 'Reportes', icon: <BarChart3 className="w-5 h-5" /> },
    { path: '/sucursales', label: 'Sucursales', icon: <MapPin className="w-5 h-5" />, adminOnly: true },
    { path: '/usuarios', label: 'Usuarios', icon: <UserCog className="w-5 h-5" />, adminOnly: true },
  ];

  const filteredMenuItems = menuItems.filter(
    (item) => !item.adminOnly || user?.rol === 'administrador'
  );

  return (
    <div className="bg-gray-800 text-white w-64 min-h-screen p-4">
      <div className="mb-8">
        <h1 className="text-2xl font-bold">Suplementos</h1>
        <p className="text-sm text-gray-400">Sistema de Gestión</p>
      </div>

      <nav>
        <ul className="space-y-2">
          {filteredMenuItems.map((item) => {
            const isActive = location.pathname === item.path;
            return (
              <li key={item.path}>
                <Link
                  to={item.path}
                  className={`flex items-center gap-3 px-4 py-3 rounded-lg transition-colors ${
                    isActive
                      ? 'bg-blue-600 text-white'
                      : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                  }`}
                >
                  {item.icon}
                  <span>{item.label}</span>
                </Link>
              </li>
            );
          })}
        </ul>
      </nav>
    </div>
  );
}
