import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import MainLayout from './components/layout/MainLayout';
import Login from './pages/auth/Login';
import Dashboard from './pages/dashboard/Dashboard';
import Ventas from './pages/ventas/Ventas';
import Productos from './pages/productos/Productos';
import Inventario from './pages/inventario/Inventario';
import Clientes from './pages/clientes/Clientes';
import Creditos from './pages/creditos/Creditos';
import Cotizaciones from './pages/cotizaciones/Cotizaciones';
import Sucursales from './pages/sucursales/Sucursales';
import Usuarios from './pages/usuarios/Usuarios';
import CierresCaja from './pages/cierres-caja/CierresCaja';
import Reportes from './pages/reportes/Reportes';

function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          <Route path="/login" element={<Login />} />

          <Route path="/dashboard" element={<MainLayout><Dashboard /></MainLayout>} />
          <Route path="/ventas" element={<MainLayout><Ventas /></MainLayout>} />
          <Route path="/productos" element={<MainLayout><Productos /></MainLayout>} />
          <Route path="/inventario" element={<MainLayout><Inventario /></MainLayout>} />
          <Route path="/clientes" element={<MainLayout><Clientes /></MainLayout>} />
          <Route path="/creditos" element={<MainLayout><Creditos /></MainLayout>} />
          <Route path="/cotizaciones" element={<MainLayout><Cotizaciones /></MainLayout>} />
          <Route path="/sucursales" element={<MainLayout><Sucursales /></MainLayout>} />
          <Route path="/usuarios" element={<MainLayout><Usuarios /></MainLayout>} />
          <Route path="/cierres-caja" element={<MainLayout><CierresCaja /></MainLayout>} />
          <Route path="/reportes" element={<MainLayout><Reportes /></MainLayout>} />

          <Route path="/" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </Router>
    </AuthProvider>
  );
}

export default App;
