import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';

interface Producto {
  id_producto: number;
  nombre: string;
  codigo_barras: string;
  categoria: string;
  marca: string;
  precio_compra: number;
  precio_venta: number;
  precio_mayoreo: number;
  stock_actual: number;
  stock_minimo: number;
  stock_maximo: number;
  requiere_lote: boolean;
  activo: boolean;
}

interface Lote {
  id_lote: number;
  numero_lote: string;
  fecha_vencimiento: string | null;
  cantidad_actual: number;
  fecha_ingreso: string;
  estado_vencimiento: 'vigente' | 'proximo_a_vencer' | 'vencido';
}

export default function ProductList() {
  const { user } = useAuth();
  const [productos, setProductos] = useState<Producto[]>([]);
  const [loading, setLoading] = useState(true);
  const [busqueda, setBusqueda] = useState('');
  const [categoriaFiltro, setCategoriaFiltro] = useState('');
  const [selectedProducto, setSelectedProducto] = useState<Producto | null>(null);
  const [lotes, setLotes] = useState<Lote[]>([]);
  const [showModalLotes, setShowModalLotes] = useState(false);

  // Cargar productos
  const cargarProductos = async () => {
    setLoading(true);
    try {
      const token = localStorage.getItem('token');
      let url = '/api/v1/productos?';

      if (user?.sucursal_id) {
        url += `sucursal=${user.sucursal_id}&`;
      }

      if (categoriaFiltro) {
        url += `categoria=${categoriaFiltro}&`;
      }

      if (busqueda) {
        url += `buscar=${encodeURIComponent(busqueda)}&`;
      }

      const response = await fetch(url, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      const data = await response.json();
      if (data.success) {
        setProductos(data.data || []);
      }
    } catch (error) {
      console.error('Error al cargar productos:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    cargarProductos();
  }, [categoriaFiltro]);

  // Buscar con debounce
  useEffect(() => {
    const timer = setTimeout(() => {
      cargarProductos();
    }, 500);

    return () => clearTimeout(timer);
  }, [busqueda]);

  // Cargar lotes de un producto
  const cargarLotes = async (producto: Producto) => {
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(
        `/api/v1/productos/${producto.id_producto}/lotes?sucursal=${user?.sucursal_id}`,
        {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        }
      );

      const data = await response.json();
      if (data.success) {
        setLotes(data.data || []);
        setSelectedProducto(producto);
        setShowModalLotes(true);
      }
    } catch (error) {
      console.error('Error al cargar lotes:', error);
    }
  };

  // Obtener clase de badge según stock
  const getStockBadgeClass = (stock: number, stockMinimo: number) => {
    if (stock <= 0) return 'bg-danger';
    if (stock <= stockMinimo) return 'bg-warning text-dark';
    return 'bg-success';
  };

  // Obtener clase de badge según estado de vencimiento
  const getVencimientoBadgeClass = (estado: string) => {
    switch (estado) {
      case 'vencido': return 'bg-danger';
      case 'proximo_a_vencer': return 'bg-warning text-dark';
      default: return 'bg-success';
    }
  };

  return (
    <div className="container-fluid py-4">
      {/* Header */}
      <div className="row mb-4">
        <div className="col">
          <h2 className="fw-bold">
            <i className="bi bi-box-seam-fill me-2 text-primary"></i>
            Gestión de Productos
          </h2>
        </div>
        <div className="col-auto">
          <button className="btn btn-primary">
            <i className="bi bi-plus-circle me-2"></i>
            Nuevo Producto
          </button>
        </div>
      </div>

      {/* Filtros */}
      <div className="card border-0 shadow-sm mb-4">
        <div className="card-body">
          <div className="row g-3">
            <div className="col-md-6">
              <label className="form-label fw-semibold">
                <i className="bi bi-search me-2"></i>
                Buscar
              </label>
              <input
                type="text"
                className="form-control"
                placeholder="Buscar por nombre, código de barras..."
                value={busqueda}
                onChange={(e) => setBusqueda(e.target.value)}
              />
            </div>
            <div className="col-md-3">
              <label className="form-label fw-semibold">
                <i className="bi bi-filter me-2"></i>
                Categoría
              </label>
              <select
                className="form-select"
                value={categoriaFiltro}
                onChange={(e) => setCategoriaFiltro(e.target.value)}
              >
                <option value="">Todas las categorías</option>
                <option value="Suplementos">Suplementos</option>
                <option value="Vitaminas">Vitaminas</option>
                <option value="Proteínas">Proteínas</option>
                <option value="Aminoácidos">Aminoácidos</option>
                <option value="Pre-Entreno">Pre-Entreno</option>
                <option value="Accesorios">Accesorios</option>
              </select>
            </div>
            <div className="col-md-3 d-flex align-items-end">
              <button className="btn btn-outline-secondary w-100" onClick={cargarProductos}>
                <i className="bi bi-arrow-clockwise me-2"></i>
                Actualizar
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Tabla de Productos */}
      <div className="card border-0 shadow-sm">
        <div className="card-body">
          {loading ? (
            <div className="text-center py-5">
              <div className="spinner-border text-primary" role="status">
                <span className="visually-hidden">Cargando...</span>
              </div>
              <p className="mt-3 text-muted">Cargando productos...</p>
            </div>
          ) : productos.length === 0 ? (
            <div className="text-center py-5">
              <i className="bi bi-inbox text-muted" style={{ fontSize: '3rem' }}></i>
              <p className="mt-3 text-muted">No se encontraron productos</p>
            </div>
          ) : (
            <div className="table-responsive">
              <table className="table table-hover align-middle">
                <thead className="table-light">
                  <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th className="text-end">Precio Venta</th>
                    <th className="text-center">Stock</th>
                    <th className="text-center">Estado</th>
                    <th className="text-center">Lotes</th>
                    <th className="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  {productos.map((producto) => (
                    <tr key={producto.id_producto}>
                      <td>
                        <code className="text-muted">{producto.codigo_barras || 'N/A'}</code>
                      </td>
                      <td>
                        <div>
                          <strong>{producto.nombre}</strong>
                          {producto.marca && (
                            <div>
                              <small className="text-muted">{producto.marca}</small>
                            </div>
                          )}
                        </div>
                      </td>
                      <td>
                        <span className="badge bg-secondary">{producto.categoria || 'Sin categoría'}</span>
                      </td>
                      <td className="text-end">
                        <strong className="text-primary">${producto.precio_venta?.toFixed(2) || '0.00'}</strong>
                        {producto.precio_mayoreo && (
                          <div>
                            <small className="text-muted">
                              Mayoreo: ${producto.precio_mayoreo.toFixed(2)}
                            </small>
                          </div>
                        )}
                      </td>
                      <td className="text-center">
                        <div className="d-flex flex-column align-items-center">
                          <span className={`badge ${getStockBadgeClass(producto.stock_actual, producto.stock_minimo)}`}>
                            {producto.stock_actual || 0} unidades
                          </span>
                          {producto.stock_minimo > 0 && (
                            <small className="text-muted mt-1">
                              Mín: {producto.stock_minimo}
                            </small>
                          )}
                        </div>
                      </td>
                      <td className="text-center">
                        {producto.activo ? (
                          <span className="badge bg-success">
                            <i className="bi bi-check-circle me-1"></i>
                            Activo
                          </span>
                        ) : (
                          <span className="badge bg-danger">
                            <i className="bi bi-x-circle me-1"></i>
                            Inactivo
                          </span>
                        )}
                      </td>
                      <td className="text-center">
                        {producto.requiere_lote ? (
                          <button
                            className="btn btn-sm btn-outline-info"
                            onClick={() => cargarLotes(producto)}
                          >
                            <i className="bi bi-list-ul me-1"></i>
                            Ver Lotes
                          </button>
                        ) : (
                          <span className="text-muted">N/A</span>
                        )}
                      </td>
                      <td className="text-center">
                        <div className="btn-group btn-group-sm">
                          <button className="btn btn-outline-primary" title="Editar">
                            <i className="bi bi-pencil"></i>
                          </button>
                          <button className="btn btn-outline-success" title="Agregar Stock">
                            <i className="bi bi-plus-circle"></i>
                          </button>
                          <button className="btn btn-outline-secondary" title="Detalles">
                            <i className="bi bi-eye"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>

      {/* Modal de Lotes */}
      {showModalLotes && (
        <div className="modal show d-block" tabIndex={-1} style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
          <div className="modal-dialog modal-lg modal-dialog-scrollable">
            <div className="modal-content">
              <div className="modal-header">
                <h5 className="modal-title">
                  <i className="bi bi-list-ul me-2"></i>
                  Lotes de: {selectedProducto?.nombre}
                </h5>
                <button
                  type="button"
                  className="btn-close"
                  onClick={() => setShowModalLotes(false)}
                ></button>
              </div>
              <div className="modal-body">
                {lotes.length === 0 ? (
                  <div className="text-center py-4 text-muted">
                    <i className="bi bi-inbox" style={{ fontSize: '2rem' }}></i>
                    <p className="mt-3">No hay lotes registrados para este producto</p>
                  </div>
                ) : (
                  <div className="table-responsive">
                    <table className="table table-hover">
                      <thead className="table-light">
                        <tr>
                          <th>Número de Lote</th>
                          <th>Fecha Ingreso</th>
                          <th>Fecha Vencimiento</th>
                          <th className="text-center">Cantidad</th>
                          <th className="text-center">Estado</th>
                        </tr>
                      </thead>
                      <tbody>
                        {lotes.map((lote) => (
                          <tr key={lote.id_lote}>
                            <td>
                              <strong>{lote.numero_lote}</strong>
                            </td>
                            <td>
                              <small>{new Date(lote.fecha_ingreso).toLocaleDateString()}</small>
                            </td>
                            <td>
                              {lote.fecha_vencimiento ? (
                                <small>{new Date(lote.fecha_vencimiento).toLocaleDateString()}</small>
                              ) : (
                                <span className="text-muted">N/A</span>
                              )}
                            </td>
                            <td className="text-center">
                              <span className="badge bg-primary">{lote.cantidad_actual}</span>
                            </td>
                            <td className="text-center">
                              <span className={`badge ${getVencimientoBadgeClass(lote.estado_vencimiento)}`}>
                                {lote.estado_vencimiento === 'vencido' && 'Vencido'}
                                {lote.estado_vencimiento === 'proximo_a_vencer' && 'Próximo a vencer'}
                                {lote.estado_vencimiento === 'vigente' && 'Vigente'}
                              </span>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
              <div className="modal-footer">
                <button className="btn btn-success">
                  <i className="bi bi-plus-circle me-2"></i>
                  Agregar Lote
                </button>
                <button
                  type="button"
                  className="btn btn-secondary"
                  onClick={() => setShowModalLotes(false)}
                >
                  Cerrar
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Resumen de Stock */}
      <div className="row mt-4">
        <div className="col-md-4">
          <div className="card border-0 shadow-sm bg-primary text-white">
            <div className="card-body">
              <div className="d-flex justify-content-between align-items-center">
                <div>
                  <h6 className="mb-1 opacity-75">Total Productos</h6>
                  <h3 className="mb-0">{productos.length}</h3>
                </div>
                <div>
                  <i className="bi bi-box-seam" style={{ fontSize: '2.5rem', opacity: 0.5 }}></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className="col-md-4">
          <div className="card border-0 shadow-sm bg-warning text-dark">
            <div className="card-body">
              <div className="d-flex justify-content-between align-items-center">
                <div>
                  <h6 className="mb-1 opacity-75">Bajo Stock</h6>
                  <h3 className="mb-0">
                    {productos.filter(p => p.stock_actual <= p.stock_minimo).length}
                  </h3>
                </div>
                <div>
                  <i className="bi bi-exclamation-triangle" style={{ fontSize: '2.5rem', opacity: 0.5 }}></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className="col-md-4">
          <div className="card border-0 shadow-sm bg-danger text-white">
            <div className="card-body">
              <div className="d-flex justify-content-between align-items-center">
                <div>
                  <h6 className="mb-1 opacity-75">Sin Stock</h6>
                  <h3 className="mb-0">
                    {productos.filter(p => p.stock_actual <= 0).length}
                  </h3>
                </div>
                <div>
                  <i className="bi bi-x-circle" style={{ fontSize: '2.5rem', opacity: 0.5 }}></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
