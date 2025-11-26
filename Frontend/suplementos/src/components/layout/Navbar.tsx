import { useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useNavigate } from 'react-router-dom';

interface NavbarProps {
  onMenuToggle: () => void;
}

export default function Navbar({ onMenuToggle }: NavbarProps) {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [showDropdown, setShowDropdown] = useState(false);

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <nav className="navbar navbar-expand navbar-light bg-white shadow-sm sticky-top">
      <div className="container-fluid px-3 px-md-4">
        {/* Botón hamburguesa para móvil */}
        <button
          className="btn btn-link d-lg-none p-0 text-dark me-3"
          onClick={onMenuToggle}
          aria-label="Toggle sidebar"
        >
          <i className="bi bi-list fs-3"></i>
        </button>

        {/* Título de bienvenida - Oculto en móvil pequeño */}
        <div className="d-none d-md-block">
          <h2 className="h5 mb-0 fw-semibold text-dark">
            Bienvenido, {user?.nombre}
          </h2>
          <small className="text-muted">
            Rol: <span className="text-capitalize fw-medium">{user?.rol}</span>
          </small>
        </div>

        {/* Título simplificado para móvil */}
        <div className="d-md-none">
          <span className="navbar-brand mb-0 h5 fw-bold">Suplementos</span>
        </div>

        {/* Spacer */}
        <div className="flex-grow-1"></div>

        {/* User dropdown */}
        <div className="dropdown">
          <button
            className="btn btn-link text-decoration-none d-flex align-items-center gap-2 p-2"
            type="button"
            onClick={() => setShowDropdown(!showDropdown)}
            aria-expanded={showDropdown}
          >
            <div className="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                 style={{ width: '40px', height: '40px' }}>
              <i className="bi bi-person-fill"></i>
            </div>
            <div className="d-none d-sm-block text-start">
              <div className="small fw-semibold text-dark text-truncate" style={{ maxWidth: '150px' }}>
                {user?.email}
              </div>
              <div className="small text-muted text-capitalize">{user?.rol}</div>
            </div>
            <i className={`bi bi-chevron-${showDropdown ? 'up' : 'down'} text-dark d-none d-sm-inline`}></i>
          </button>

          {showDropdown && (
            <>
              <div
                className="position-fixed top-0 start-0 w-100 h-100"
                style={{ zIndex: 1040 }}
                onClick={() => setShowDropdown(false)}
              ></div>
              <div className="dropdown-menu dropdown-menu-end show position-absolute shadow-lg border-0"
                   style={{ zIndex: 1050, minWidth: '200px' }}>
                <div className="dropdown-item-text border-bottom pb-2 mb-2">
                  <div className="fw-semibold text-dark">{user?.nombre}</div>
                  <div className="small text-muted">{user?.email}</div>
                </div>
                <button
                  onClick={handleLogout}
                  className="dropdown-item d-flex align-items-center gap-2 text-danger"
                >
                  <i className="bi bi-box-arrow-right"></i>
                  Cerrar Sesión
                </button>
              </div>
            </>
          )}
        </div>
      </div>
    </nav>
  );
}
