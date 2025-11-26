# ARQUITECTURA DEL FRONTEND - REACT SPA

## 1. ESTRUCTURA DEL PROYECTO

```
frontend/
├── public/
│   ├── index.html
│   └── favicon.ico
├── src/
│   ├── components/           # Componentes reutilizables
│   │   ├── common/           # Componentes comunes
│   │   │   ├── Button.jsx
│   │   │   ├── Input.jsx
│   │   │   ├── Modal.jsx
│   │   │   ├── Table.jsx
│   │   │   ├── Loader.jsx
│   │   │   ├── Alert.jsx
│   │   │   └── Pagination.jsx
│   │   ├── layout/           # Componentes de layout
│   │   │   ├── Navbar.jsx
│   │   │   ├── Sidebar.jsx
│   │   │   ├── Header.jsx
│   │   │   └── Footer.jsx
│   │   └── forms/            # Componentes de formularios
│   │       ├── ProductoForm.jsx
│   │       ├── ClienteForm.jsx
│   │       ├── VentaForm.jsx
│   │       └── CotizacionForm.jsx
│   ├── pages/                # Páginas/vistas principales
│   │   ├── auth/
│   │   │   ├── Login.jsx
│   │   │   └── ChangePassword.jsx
│   │   ├── dashboard/
│   │   │   └── Dashboard.jsx
│   │   ├── sucursales/
│   │   │   ├── SucursalesList.jsx
│   │   │   └── SucursalDetail.jsx
│   │   ├── productos/
│   │   │   ├── ProductosList.jsx
│   │   │   ├── ProductoDetail.jsx
│   │   │   └── LotesManagement.jsx
│   │   ├── inventario/
│   │   │   ├── InventarioList.jsx
│   │   │   ├── MovimientoStock.jsx
│   │   │   └── Traslados.jsx
│   │   ├── clientes/
│   │   │   ├── ClientesList.jsx
│   │   │   └── ClienteDetail.jsx
│   │   ├── ventas/
│   │   │   ├── VentasList.jsx
│   │   │   ├── NuevaVenta.jsx
│   │   │   └── VentaDetail.jsx
│   │   ├── cotizaciones/
│   │   │   ├── CotizacionesList.jsx
│   │   │   ├── NuevaCotizacion.jsx
│   │   │   └── CotizacionDetail.jsx
│   │   ├── creditos/
│   │   │   ├── CreditosList.jsx
│   │   │   ├── CreditoDetail.jsx
│   │   │   └── RegistrarPago.jsx
│   │   ├── caja/
│   │   │   ├── CierreCaja.jsx
│   │   │   └── CierreCajaDetail.jsx
│   │   └── reportes/
│   │       ├── ReporteInventario.jsx
│   │       ├── ReporteVentas.jsx
│   │       ├── ReporteProductosPorVencer.jsx
│   │       ├── ReporteCuadreCaja.jsx
│   │       ├── ReporteConvenios.jsx
│   │       └── ReporteCreditos.jsx
│   ├── services/             # Servicios de API
│   │   ├── api.js            # Configuración base de axios
│   │   ├── authService.js
│   │   ├── usuariosService.js
│   │   ├── sucursalesService.js
│   │   ├── productosService.js
│   │   ├── inventarioService.js
│   │   ├── clientesService.js
│   │   ├── ventasService.js
│   │   ├── cotizacionesService.js
│   │   ├── creditosService.js
│   │   ├── cajaService.js
│   │   └── reportesService.js
│   ├── contexts/             # Contextos de React
│   │   ├── AuthContext.jsx   # Contexto de autenticación
│   │   └── AppContext.jsx    # Contexto global de la app
│   ├── hooks/                # Custom hooks
│   │   ├── useAuth.js
│   │   ├── useApi.js
│   │   └── usePagination.js
│   ├── utils/                # Utilidades
│   │   ├── constants.js
│   │   ├── formatters.js     # Formateadores de fecha, moneda, etc.
│   │   ├── validators.js     # Validaciones
│   │   └── helpers.js        # Funciones auxiliares
│   ├── styles/               # Estilos globales
│   │   ├── global.css
│   │   └── variables.css
│   ├── App.jsx               # Componente principal
│   ├── routes.jsx            # Configuración de rutas
│   └── index.js              # Punto de entrada
├── package.json
└── README.md
```

---

## 2. TECNOLOGÍAS Y LIBRERÍAS RECOMENDADAS

```json
{
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "react-router-dom": "^6.20.0",
    "axios": "^1.6.2",
    "date-fns": "^2.30.0",
    "@tanstack/react-query": "^5.12.0"
  },
  "devDependencies": {
    "vite": "^5.0.0",
    "@vitejs/plugin-react": "^4.2.0"
  }
}
```

### Descripción de librerías:
- **react-router-dom**: Navegación SPA
- **axios**: Cliente HTTP para consumir la API
- **date-fns**: Manejo de fechas
- **@tanstack/react-query**: Manejo de estado del servidor y cache
- **vite**: Herramienta de build rápida para desarrollo

---

## 3. CONFIGURACIÓN INICIAL

### 3.1 Archivo `src/services/api.js`
Configuración base de Axios con interceptores.

```javascript
import axios from 'axios';

const API_BASE_URL = 'http://localhost/api/v1';

// Crear instancia de axios
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Interceptor para agregar token a las peticiones
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor para manejar respuestas y errores
api.interceptors.response.use(
  (response) => {
    return response.data;
  },
  (error) => {
    if (error.response) {
      // Token expirado o inválido
      if (error.response.status === 401) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = '/login';
      }

      // Retornar mensaje de error del servidor
      return Promise.reject(
        error.response.data.message || 'Error en la petición'
      );
    } else if (error.request) {
      return Promise.reject('No se pudo conectar con el servidor');
    } else {
      return Promise.reject('Error desconocido');
    }
  }
);

export default api;
```

---

### 3.2 Archivo `src/contexts/AuthContext.jsx`
Contexto de autenticación.

```javascript
import React, { createContext, useState, useEffect } from 'react';
import { authService } from '../services/authService';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  useEffect(() => {
    // Verificar si hay un usuario guardado en localStorage
    const storedUser = localStorage.getItem('user');
    const token = localStorage.getItem('token');

    if (storedUser && token) {
      setUser(JSON.parse(storedUser));
      setIsAuthenticated(true);
    }

    setLoading(false);
  }, []);

  const login = async (credentials) => {
    try {
      const response = await authService.login(credentials);
      const { token, usuario } = response.data;

      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(usuario));

      setUser(usuario);
      setIsAuthenticated(true);

      return { success: true };
    } catch (error) {
      return { success: false, error };
    }
  };

  const logout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    setUser(null);
    setIsAuthenticated(false);
  };

  const hasRole = (roles) => {
    if (!user) return false;
    return roles.includes(user.rol);
  };

  const isAdmin = () => {
    return user?.rol === 'Administrador';
  };

  const value = {
    user,
    loading,
    isAuthenticated,
    login,
    logout,
    hasRole,
    isAdmin
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};
```

---

### 3.3 Archivo `src/hooks/useAuth.js`
Hook personalizado para usar el contexto de autenticación.

```javascript
import { useContext } from 'react';
import { AuthContext } from '../contexts/AuthContext';

export const useAuth = () => {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error('useAuth debe ser usado dentro de un AuthProvider');
  }

  return context;
};
```

---

### 3.4 Archivo `src/services/authService.js`
Servicio de autenticación.

```javascript
import api from './api';

export const authService = {
  login: (credentials) => {
    return api.post('/auth/login', credentials);
  },

  logout: () => {
    return api.post('/auth/logout');
  },

  me: () => {
    return api.get('/auth/me');
  },

  changePassword: (data) => {
    return api.post('/auth/change-password', data);
  }
};
```

---

## 4. EJEMPLO DE PÁGINA: LOGIN

### Archivo `src/pages/auth/Login.jsx`

```javascript
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';

const Login = () => {
  const navigate = useNavigate();
  const { login } = useAuth();

  const [formData, setFormData] = useState({
    usuario: '',
    contrasena: ''
  });

  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    const result = await login(formData);

    if (result.success) {
      navigate('/dashboard');
    } else {
      setError(result.error);
      setLoading(false);
    }
  };

  return (
    <div className="login-container">
      <div className="login-card">
        <h1>Sistema de Gestión de Inventario</h1>
        <h2>Iniciar Sesión</h2>

        {error && (
          <div className="alert alert-error">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label>Usuario</label>
            <input
              type="text"
              name="usuario"
              value={formData.usuario}
              onChange={handleChange}
              placeholder="Ingrese su usuario"
              required
              disabled={loading}
            />
          </div>

          <div className="form-group">
            <label>Contraseña</label>
            <input
              type="password"
              name="contrasena"
              value={formData.contrasena}
              onChange={handleChange}
              placeholder="Ingrese su contraseña"
              required
              disabled={loading}
            />
          </div>

          <button
            type="submit"
            className="btn btn-primary btn-block"
            disabled={loading}
          >
            {loading ? 'Iniciando sesión...' : 'Iniciar Sesión'}
          </button>
        </form>
      </div>
    </div>
  );
};

export default Login;
```

---

## 5. EJEMPLO DE PÁGINA: NUEVA VENTA

### Archivo `src/pages/ventas/NuevaVenta.jsx`

```javascript
import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import { ventasService } from '../../services/ventasService';
import { productosService } from '../../services/productosService';
import { clientesService } from '../../services/clientesService';

const NuevaVenta = () => {
  const navigate = useNavigate();
  const { user } = useAuth();

  const [formData, setFormData] = useState({
    id_sucursal: user.id_sucursal,
    id_cliente: null,
    tipo_venta: 'CONTADO',
    metodo_pago: 'efectivo',
    descuento: 0,
    observaciones: ''
  });

  const [productos, setProductos] = useState([]);
  const [productosDisponibles, setProductosDisponibles] = useState([]);
  const [clientes, setClientes] = useState([]);
  const [busquedaProducto, setBusquedaProducto] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    cargarClientes();
  }, []);

  const cargarClientes = async () => {
    try {
      const response = await clientesService.getAll();
      setClientes(response.data);
    } catch (error) {
      console.error('Error al cargar clientes:', error);
    }
  };

  const buscarProductos = async (termino) => {
    if (termino.length < 2) {
      setProductosDisponibles([]);
      return;
    }

    try {
      const response = await productosService.search(termino);
      setProductosDisponibles(response.data.productos);
    } catch (error) {
      console.error('Error al buscar productos:', error);
    }
  };

  const agregarProducto = (producto) => {
    // Verificar si el producto ya está en la lista
    const existe = productos.find(p => p.id_producto === producto.id_producto);

    if (existe) {
      // Incrementar cantidad
      setProductos(productos.map(p =>
        p.id_producto === producto.id_producto
          ? { ...p, cantidad: p.cantidad + 1 }
          : p
      ));
    } else {
      // Agregar nuevo producto
      setProductos([
        ...productos,
        {
          id_producto: producto.id_producto,
          nombre: producto.nombre,
          precio_unitario: producto.precio_base,
          cantidad: 1,
          descuento: 0,
          id_lote: null // Se debe seleccionar si el producto requiere lote
        }
      ]);
    }

    setBusquedaProducto('');
    setProductosDisponibles([]);
  };

  const eliminarProducto = (index) => {
    setProductos(productos.filter((_, i) => i !== index));
  };

  const actualizarCantidad = (index, cantidad) => {
    setProductos(productos.map((p, i) =>
      i === index ? { ...p, cantidad: parseFloat(cantidad) || 0 } : p
    ));
  };

  const actualizarPrecio = (index, precio) => {
    setProductos(productos.map((p, i) =>
      i === index ? { ...p, precio_unitario: parseFloat(precio) || 0 } : p
    ));
  };

  const calcularSubtotal = () => {
    return productos.reduce((sum, p) =>
      sum + (p.cantidad * p.precio_unitario), 0
    );
  };

  const calcularTotal = () => {
    return calcularSubtotal() - parseFloat(formData.descuento || 0);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (productos.length === 0) {
      setError('Debe agregar al menos un producto');
      return;
    }

    if (formData.tipo_venta === 'CREDITO' && !formData.id_cliente) {
      setError('Debe seleccionar un cliente para ventas a crédito');
      return;
    }

    setLoading(true);
    setError('');

    try {
      const ventaData = {
        ...formData,
        productos
      };

      const response = await ventasService.create(ventaData);

      alert('Venta registrada exitosamente');
      navigate(`/ventas/${response.data.id_venta}`);
    } catch (error) {
      setError(error);
      setLoading(false);
    }
  };

  return (
    <div className="nueva-venta-page">
      <h1>Nueva Venta</h1>

      {error && (
        <div className="alert alert-error">
          {error}
        </div>
      )}

      <form onSubmit={handleSubmit}>
        <div className="venta-header">
          <div className="form-row">
            <div className="form-group">
              <label>Tipo de Venta</label>
              <select
                name="tipo_venta"
                value={formData.tipo_venta}
                onChange={(e) => setFormData({ ...formData, tipo_venta: e.target.value })}
                required
              >
                <option value="CONTADO">Contado</option>
                <option value="CREDITO">Crédito</option>
              </select>
            </div>

            <div className="form-group">
              <label>Cliente</label>
              <select
                name="id_cliente"
                value={formData.id_cliente || ''}
                onChange={(e) => setFormData({ ...formData, id_cliente: e.target.value || null })}
                required={formData.tipo_venta === 'CREDITO'}
              >
                <option value="">Cliente genérico</option>
                {clientes.map(cliente => (
                  <option key={cliente.id_cliente} value={cliente.id_cliente}>
                    {cliente.nombre_completo} - {cliente.identificacion}
                  </option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label>Método de Pago</label>
              <select
                name="metodo_pago"
                value={formData.metodo_pago}
                onChange={(e) => setFormData({ ...formData, metodo_pago: e.target.value })}
                required
              >
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
              </select>
            </div>
          </div>
        </div>

        <div className="productos-section">
          <h3>Productos</h3>

          <div className="buscar-producto">
            <input
              type="text"
              placeholder="Buscar producto por nombre o código..."
              value={busquedaProducto}
              onChange={(e) => {
                setBusquedaProducto(e.target.value);
                buscarProductos(e.target.value);
              }}
            />

            {productosDisponibles.length > 0 && (
              <div className="productos-dropdown">
                {productosDisponibles.map(producto => (
                  <div
                    key={producto.id_producto}
                    className="producto-item"
                    onClick={() => agregarProducto(producto)}
                  >
                    <strong>{producto.nombre}</strong>
                    <span>Q {producto.precio_base.toFixed(2)}</span>
                  </div>
                ))}
              </div>
            )}
          </div>

          <table className="productos-table">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              {productos.map((producto, index) => (
                <tr key={index}>
                  <td>{producto.nombre}</td>
                  <td>
                    <input
                      type="number"
                      min="1"
                      step="0.01"
                      value={producto.cantidad}
                      onChange={(e) => actualizarCantidad(index, e.target.value)}
                      style={{ width: '80px' }}
                    />
                  </td>
                  <td>
                    <input
                      type="number"
                      min="0"
                      step="0.01"
                      value={producto.precio_unitario}
                      onChange={(e) => actualizarPrecio(index, e.target.value)}
                      style={{ width: '100px' }}
                    />
                  </td>
                  <td>Q {(producto.cantidad * producto.precio_unitario).toFixed(2)}</td>
                  <td>
                    <button
                      type="button"
                      className="btn btn-danger btn-sm"
                      onClick={() => eliminarProducto(index)}
                    >
                      Eliminar
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="venta-footer">
          <div className="totales">
            <div className="total-row">
              <span>Subtotal:</span>
              <strong>Q {calcularSubtotal().toFixed(2)}</strong>
            </div>

            <div className="total-row">
              <span>Descuento:</span>
              <input
                type="number"
                min="0"
                step="0.01"
                value={formData.descuento}
                onChange={(e) => setFormData({ ...formData, descuento: e.target.value })}
                style={{ width: '120px', textAlign: 'right' }}
              />
            </div>

            <div className="total-row total-final">
              <span>TOTAL:</span>
              <strong>Q {calcularTotal().toFixed(2)}</strong>
            </div>
          </div>

          <div className="form-actions">
            <button
              type="button"
              className="btn btn-secondary"
              onClick={() => navigate('/ventas')}
              disabled={loading}
            >
              Cancelar
            </button>
            <button
              type="submit"
              className="btn btn-primary"
              disabled={loading || productos.length === 0}
            >
              {loading ? 'Procesando...' : 'Registrar Venta'}
            </button>
          </div>
        </div>
      </form>
    </div>
  );
};

export default NuevaVenta;
```

---

## 6. CONFIGURACIÓN DE RUTAS

### Archivo `src/routes.jsx`

```javascript
import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { useAuth } from './hooks/useAuth';

// Layout
import MainLayout from './components/layout/MainLayout';

// Páginas
import Login from './pages/auth/Login';
import Dashboard from './pages/dashboard/Dashboard';
import ProductosList from './pages/productos/ProductosList';
import NuevaVenta from './pages/ventas/NuevaVenta';
import VentasList from './pages/ventas/VentasList';
// ... importar otras páginas

const PrivateRoute = ({ children, requiredRoles }) => {
  const { isAuthenticated, loading, hasRole } = useAuth();

  if (loading) {
    return <div>Cargando...</div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" />;
  }

  if (requiredRoles && !hasRole(requiredRoles)) {
    return <Navigate to="/dashboard" />;
  }

  return children;
};

const AppRoutes = () => {
  return (
    <BrowserRouter>
      <Routes>
        {/* Rutas públicas */}
        <Route path="/login" element={<Login />} />

        {/* Rutas privadas */}
        <Route
          path="/"
          element={
            <PrivateRoute>
              <MainLayout />
            </PrivateRoute>
          }
        >
          <Route index element={<Navigate to="/dashboard" />} />
          <Route path="dashboard" element={<Dashboard />} />

          {/* Productos */}
          <Route path="productos" element={<ProductosList />} />

          {/* Ventas */}
          <Route path="ventas" element={<VentasList />} />
          <Route path="ventas/nueva" element={<NuevaVenta />} />

          {/* Administración (solo admin) */}
          <Route
            path="usuarios"
            element={
              <PrivateRoute requiredRoles={['Administrador']}>
                <div>Gestión de usuarios</div>
              </PrivateRoute>
            }
          />

          {/* ... más rutas */}
        </Route>

        {/* Ruta 404 */}
        <Route path="*" element={<div>Página no encontrada</div>} />
      </Routes>
    </BrowserRouter>
  );
};

export default AppRoutes;
```

---

## 7. SERVICIOS DE API

### Ejemplo: `src/services/ventasService.js`

```javascript
import api from './api';

export const ventasService = {
  getAll: (params = {}) => {
    return api.get('/ventas', { params });
  },

  getById: (id) => {
    return api.get(`/ventas/${id}`);
  },

  create: (data) => {
    return api.post('/ventas', data);
  }
};
```

---

## 8. UTILIDADES

### Archivo `src/utils/formatters.js`

```javascript
// Formatear moneda
export const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-GT', {
    style: 'currency',
    currency: 'GTQ'
  }).format(amount);
};

// Formatear fecha
export const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-GT');
};

// Formatear fecha y hora
export const formatDateTime = (date) => {
  return new Date(date).toLocaleString('es-GT');
};
```

---

## 9. NOTAS FINALES

1. **Estilos CSS**: Puedes usar CSS puro, CSS Modules, o styled-components según preferencia
2. **Estado Global**: Para estado más complejo, considera usar React Query o Zustand
3. **Validación de Formularios**: Considera usar libraries como React Hook Form o Formik
4. **Notificaciones**: Agrega una librería como react-toastify para notificaciones
5. **Tablas**: Usa react-table para tablas más complejas con filtros y ordenamiento

El frontend está diseñado para ser escalable y mantenible, siguiendo las mejores prácticas de React.
