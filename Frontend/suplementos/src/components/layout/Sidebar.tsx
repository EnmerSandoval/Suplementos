import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';

interface MenuItem {
  path: string;
  label: string;
  icon: string;
  adminOnly?: boolean;
}

interface SidebarProps {
  isOpen?: boolean;
  onClose?: () => void;
}

export default function Sidebar({ isOpen = true, onClose }: SidebarProps) {
  const location = useLocation();
  const { user } = useAuth();

  const menuItems: MenuItem[] = [
    { path: '/dashboard', label: 'Dashboard', icon: 'bi-speedometer2' },
    { path: '/ventas', label: 'Ventas (POS)', icon: 'bi-cart3' },
    { path: '/productos', label: 'Productos', icon: 'bi-box-seam' },
    { path: '/inventario', label: 'Inventario', icon: 'bi-stack' },
    { path: '/clientes', label: 'Clientes', icon: 'bi-people' },
    { path: '/creditos', label: 'Créditos', icon: 'bi-credit-card' },
    { path: '/cotizaciones', label: 'Cotizaciones', icon: 'bi-file-text' },
    { path: '/cierres-caja', label: 'Cierres de Caja', icon: 'bi-cash-stack' },
    { path: '/reportes', label: 'Reportes', icon: 'bi-bar-chart' },
    { path: '/sucursales', label: 'Sucursales', icon: 'bi-geo-alt', adminOnly: true },
    { path: '/usuarios', label: 'Usuarios', icon: 'bi-person-gear', adminOnly: true },
  ];

  const filteredMenuItems = menuItems.filter(
    (item) => !item.adminOnly || user?.rol === 'administrador'
  );

  return (
    <>
      {/* Overlay para móvil */}
      {isOpen && (
        <div
          className="offcanvas-backdrop fade show d-lg-none"
          onClick={onClose}
          style={{ zIndex: 1040 }}
        ></div>
      )}

      {/* Sidebar */}
      <div
        className={`bg-dark text-white vh-100 position-fixed position-lg-sticky top-0 start-0 overflow-auto ${
          isOpen ? 'translate-x-0' : 'translate-x-n100'
        }`}
        style={{
          width: '280px',
          zIndex: 1050,
          transform: isOpen || window.innerWidth >= 992 ? 'translateX(0)' : 'translateX(-100%)',
          transition: 'transform 0.3s ease-in-out',
        }}
      >
        <div className="p-4">
          {/* Header con botón cerrar en móvil */}
          <div className="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h1 className="h3 fw-bold mb-1">
                <i className="bi bi-box-seam me-2"></i>
                Suplementos
              </h1>
              <p className="text-white-50 small mb-0">Sistema de Gestión</p>
            </div>
            <button
              type="button"
              className="btn-close btn-close-white d-lg-none"
              onClick={onClose}
              aria-label="Close"
            ></button>
          </div>

          {/* Información del usuario */}
          <div className="bg-dark bg-opacity-50 rounded p-3 mb-4">
            <div className="d-flex align-items-center">
              <div className="bg-primary rounded-circle p-2 me-3">
                <i className="bi bi-person-fill text-white"></i>
              </div>
              <div className="flex-grow-1 text-truncate">
                <p className="mb-0 fw-semibold small text-truncate">{user?.nombre}</p>
                <p className="mb-0 text-white-50 small text-capitalize">{user?.rol}</p>
              </div>
            </div>
          </div>

          {/* Menú de navegación */}
          <nav>
            <ul className="nav flex-column gap-1">
              {filteredMenuItems.map((item) => {
                const isActive = location.pathname === item.path;
                return (
                  <li key={item.path} className="nav-item">
                    <Link
                      to={item.path}
                      className={`nav-link d-flex align-items-center gap-3 rounded px-3 py-2 ${
                        isActive
                          ? 'bg-primary text-white'
                          : 'text-white-50 hover-bg-secondary'
                      }`}
                      style={{
                        backgroundColor: isActive ? undefined : 'transparent',
                        transition: 'all 0.2s',
                      }}
                      onClick={onClose}
                      onMouseEnter={(e) => {
                        if (!isActive) {
                          e.currentTarget.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
                          e.currentTarget.style.color = 'white';
                        }
                      }}
                      onMouseLeave={(e) => {
                        if (!isActive) {
                          e.currentTarget.style.backgroundColor = 'transparent';
                          e.currentTarget.style.color = 'rgba(255, 255, 255, 0.5)';
                        }
                      }}
                    >
                      <i className={`bi ${item.icon} fs-5`}></i>
                      <span className="fw-medium">{item.label}</span>
                    </Link>
                  </li>
                );
              })}
            </ul>
          </nav>
        </div>
      </div>
    </>
  );
}
