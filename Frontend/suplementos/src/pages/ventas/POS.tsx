import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';

interface ProductoPOS {
  id_producto: number;
  nombre: string;
  codigo_barras: string;
  precio_venta: number;
  precio_mayoreo: number;
  stock_disponible: number;
  requiere_lote: boolean;
}

interface ItemCarrito {
  id_producto: number;
  nombre: string;
  precio_unitario: number;
  cantidad: number;
  descuento: number;
  subtotal: number;
}

export default function POS() {
  const { user } = useAuth();
  const [busqueda, setBusqueda] = useState('');
  const [productos, setProductos] = useState<ProductoPOS[]>([]);
  const [carrito, setCarrito] = useState<ItemCarrito[]>([]);
  const [loading, setLoading] = useState(false);
  const [metodoPago, setMetodoPago] = useState('efectivo');
  const [descuentoGlobal, setDescuentoGlobal] = useState(0);
  const [montoPagado, setMontoPagado] = useState(0);
  const [showModalPago, setShowModalPago] = useState(false);

  // Buscar productos
  const buscarProductos = async (query: string) => {
    if (!query || query.length < 2) {
      setProductos([]);
      return;
    }

    setLoading(true);
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(
        `/api/v1/productos/search?q=${encodeURIComponent(query)}&sucursal=${user?.sucursal_id}`,
        {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        }
      );

      const data = await response.json();
      if (data.success) {
        setProductos(data.data || []);
      }
    } catch (error) {
      console.error('Error al buscar productos:', error);
    } finally {
      setLoading(false);
    }
  };

  // Debounce para la búsqueda
  useEffect(() => {
    const timer = setTimeout(() => {
      buscarProductos(busqueda);
    }, 300);

    return () => clearTimeout(timer);
  }, [busqueda]);

  // Agregar producto al carrito
  const agregarAlCarrito = (producto: ProductoPOS) => {
    if (producto.stock_disponible <= 0) {
      alert('Producto sin stock disponible');
      return;
    }

    const itemExistente = carrito.find(item => item.id_producto === producto.id_producto);

    if (itemExistente) {
      // Incrementar cantidad
      const nuevoCarrito = carrito.map(item =>
        item.id_producto === producto.id_producto
          ? {
              ...item,
              cantidad: item.cantidad + 1,
              subtotal: (item.cantidad + 1) * item.precio_unitario - item.descuento
            }
          : item
      );
      setCarrito(nuevoCarrito);
    } else {
      // Agregar nuevo item
      const nuevoItem: ItemCarrito = {
        id_producto: producto.id_producto,
        nombre: producto.nombre,
        precio_unitario: producto.precio_venta,
        cantidad: 1,
        descuento: 0,
        subtotal: producto.precio_venta
      };
      setCarrito([...carrito, nuevoItem]);
    }

    // Limpiar búsqueda
    setBusqueda('');
    setProductos([]);
  };

  // Actualizar cantidad de un item
  const actualizarCantidad = (id_producto: number, nuevaCantidad: number) => {
    if (nuevaCantidad <= 0) {
      eliminarItem(id_producto);
      return;
    }

    const nuevoCarrito = carrito.map(item =>
      item.id_producto === id_producto
        ? {
            ...item,
            cantidad: nuevaCantidad,
            subtotal: nuevaCantidad * item.precio_unitario - item.descuento
          }
        : item
    );
    setCarrito(nuevoCarrito);
  };

  // Eliminar item del carrito
  const eliminarItem = (id_producto: number) => {
    setCarrito(carrito.filter(item => item.id_producto !== id_producto));
  };

  // Calcular totales
  const calcularSubtotal = () => {
    return carrito.reduce((sum, item) => sum + item.subtotal, 0);
  };

  const calcularTotal = () => {
    return calcularSubtotal() - descuentoGlobal;
  };

  const calcularCambio = () => {
    const total = calcularTotal();
    return montoPagado > total ? montoPagado - total : 0;
  };

  // Finalizar venta
  const finalizarVenta = async () => {
    if (carrito.length === 0) {
      alert('El carrito está vacío');
      return;
    }

    const total = calcularTotal();

    if (metodoPago === 'efectivo' && montoPagado < total) {
      alert('El monto pagado es insuficiente');
      return;
    }

    setLoading(true);
    try {
      const token = localStorage.getItem('token');

      const ventaData = {
        id_sucursal: user?.sucursal_id,
        items: carrito.map(item => ({
          id_producto: item.id_producto,
          cantidad: item.cantidad,
          precio_unitario: item.precio_unitario,
          descuento: item.descuento,
          subtotal: item.subtotal,
          id_sucursal: user?.sucursal_id
        })),
        subtotal: calcularSubtotal(),
        descuento: descuentoGlobal,
        impuesto: 0,
        total: total,
        metodo_pago: metodoPago,
        tipo_venta: 'contado',
        estado: 'completada'
      };

      const response = await fetch('/api/v1/ventas', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(ventaData)
      });

      const data = await response.json();

      if (data.success) {
        alert('Venta realizada exitosamente');
        // Limpiar carrito y resetear
        setCarrito([]);
        setDescuentoGlobal(0);
        setMontoPagado(0);
        setShowModalPago(false);
      } else {
        alert(data.mensaje || 'Error al procesar la venta');
      }
    } catch (error) {
      console.error('Error al finalizar venta:', error);
      alert('Error al procesar la venta');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container-fluid py-4">
      <div className="row mb-4">
        <div className="col">
          <h2 className="fw-bold">
            <i className="bi bi-cart-check-fill me-2 text-primary"></i>
            Punto de Venta
          </h2>
        </div>
      </div>

      <div className="row g-4">
        {/* Columna Izquierda - Búsqueda y Productos */}
        <div className="col-lg-7">
          <div className="card border-0 shadow-sm h-100">
            <div className="card-body">
              {/* Buscador */}
              <div className="mb-4">
                <label className="form-label fw-semibold">
                  <i className="bi bi-search me-2"></i>
                  Buscar Producto
                </label>
                <div className="input-group input-group-lg">
                  <span className="input-group-text bg-white">
                    <i className="bi bi-upc-scan"></i>
                  </span>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Código de barras o nombre del producto..."
                    value={busqueda}
                    onChange={(e) => setBusqueda(e.target.value)}
                    autoFocus
                  />
                </div>
              </div>

              {/* Resultados de búsqueda */}
              {loading && (
                <div className="text-center py-4">
                  <div className="spinner-border text-primary" role="status">
                    <span className="visually-hidden">Buscando...</span>
                  </div>
                </div>
              )}

              {productos.length > 0 && (
                <div className="list-group mb-4">
                  {productos.map((producto) => (
                    <button
                      key={producto.id_producto}
                      type="button"
                      className="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                      onClick={() => agregarAlCarrito(producto)}
                      disabled={producto.stock_disponible <= 0}
                    >
                      <div>
                        <h6 className="mb-1">{producto.nombre}</h6>
                        <small className="text-muted">
                          Código: {producto.codigo_barras || 'N/A'} |
                          Stock: <span className={producto.stock_disponible <= 5 ? 'text-danger' : 'text-success'}>
                            {producto.stock_disponible}
                          </span>
                        </small>
                      </div>
                      <div className="text-end">
                        <h5 className="mb-0 text-primary">
                          ${producto.precio_venta.toFixed(2)}
                        </h5>
                      </div>
                    </button>
                  ))}
                </div>
              )}

              {/* Carrito de compras */}
              <div className="mt-4">
                <h5 className="fw-semibold mb-3">
                  <i className="bi bi-cart3 me-2"></i>
                  Carrito de Compras
                </h5>

                {carrito.length === 0 ? (
                  <div className="text-center py-5 text-muted">
                    <i className="bi bi-cart-x" style={{ fontSize: '3rem' }}></i>
                    <p className="mt-3">El carrito está vacío</p>
                    <small>Busca y agrega productos para iniciar la venta</small>
                  </div>
                ) : (
                  <div className="table-responsive">
                    <table className="table table-hover">
                      <thead className="table-light">
                        <tr>
                          <th>Producto</th>
                          <th className="text-center">Cantidad</th>
                          <th className="text-end">Precio</th>
                          <th className="text-end">Subtotal</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                        {carrito.map((item) => (
                          <tr key={item.id_producto}>
                            <td>
                              <strong>{item.nombre}</strong>
                            </td>
                            <td className="text-center">
                              <div className="input-group input-group-sm" style={{ width: '120px', margin: '0 auto' }}>
                                <button
                                  className="btn btn-outline-secondary"
                                  onClick={() => actualizarCantidad(item.id_producto, item.cantidad - 1)}
                                >
                                  <i className="bi bi-dash"></i>
                                </button>
                                <input
                                  type="number"
                                  className="form-control text-center"
                                  value={item.cantidad}
                                  onChange={(e) => actualizarCantidad(item.id_producto, parseInt(e.target.value) || 0)}
                                  min="1"
                                />
                                <button
                                  className="btn btn-outline-secondary"
                                  onClick={() => actualizarCantidad(item.id_producto, item.cantidad + 1)}
                                >
                                  <i className="bi bi-plus"></i>
                                </button>
                              </div>
                            </td>
                            <td className="text-end">${item.precio_unitario.toFixed(2)}</td>
                            <td className="text-end fw-bold">${item.subtotal.toFixed(2)}</td>
                            <td className="text-center">
                              <button
                                className="btn btn-sm btn-outline-danger"
                                onClick={() => eliminarItem(item.id_producto)}
                              >
                                <i className="bi bi-trash"></i>
                              </button>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Columna Derecha - Resumen y Pago */}
        <div className="col-lg-5">
          <div className="card border-0 shadow-sm">
            <div className="card-body">
              <h5 className="fw-semibold mb-4">
                <i className="bi bi-receipt me-2"></i>
                Resumen de Venta
              </h5>

              {/* Totales */}
              <div className="bg-light rounded p-3 mb-3">
                <div className="d-flex justify-content-between mb-2">
                  <span>Subtotal:</span>
                  <span className="fw-semibold">${calcularSubtotal().toFixed(2)}</span>
                </div>
                <div className="d-flex justify-content-between mb-2">
                  <span>Descuento:</span>
                  <div className="input-group input-group-sm" style={{ width: '150px' }}>
                    <span className="input-group-text">$</span>
                    <input
                      type="number"
                      className="form-control text-end"
                      value={descuentoGlobal}
                      onChange={(e) => setDescuentoGlobal(parseFloat(e.target.value) || 0)}
                      min="0"
                      step="0.01"
                    />
                  </div>
                </div>
                <hr />
                <div className="d-flex justify-content-between">
                  <span className="fs-5 fw-bold">TOTAL:</span>
                  <span className="fs-4 fw-bold text-primary">${calcularTotal().toFixed(2)}</span>
                </div>
              </div>

              {/* Método de Pago */}
              <div className="mb-4">
                <label className="form-label fw-semibold">Método de Pago</label>
                <div className="btn-group w-100" role="group">
                  <input
                    type="radio"
                    className="btn-check"
                    name="metodoPago"
                    id="efectivo"
                    value="efectivo"
                    checked={metodoPago === 'efectivo'}
                    onChange={(e) => setMetodoPago(e.target.value)}
                  />
                  <label className="btn btn-outline-primary" htmlFor="efectivo">
                    <i className="bi bi-cash-coin me-2"></i>
                    Efectivo
                  </label>

                  <input
                    type="radio"
                    className="btn-check"
                    name="metodoPago"
                    id="tarjeta"
                    value="tarjeta"
                    checked={metodoPago === 'tarjeta'}
                    onChange={(e) => setMetodoPago(e.target.value)}
                  />
                  <label className="btn btn-outline-primary" htmlFor="tarjeta">
                    <i className="bi bi-credit-card me-2"></i>
                    Tarjeta
                  </label>

                  <input
                    type="radio"
                    className="btn-check"
                    name="metodoPago"
                    id="transferencia"
                    value="transferencia"
                    checked={metodoPago === 'transferencia'}
                    onChange={(e) => setMetodoPago(e.target.value)}
                  />
                  <label className="btn btn-outline-primary" htmlFor="transferencia">
                    <i className="bi bi-bank me-2"></i>
                    Transferencia
                  </label>
                </div>
              </div>

              {/* Monto Pagado (solo para efectivo) */}
              {metodoPago === 'efectivo' && (
                <div className="mb-4">
                  <label className="form-label fw-semibold">Monto Pagado</label>
                  <div className="input-group input-group-lg">
                    <span className="input-group-text">$</span>
                    <input
                      type="number"
                      className="form-control"
                      value={montoPagado}
                      onChange={(e) => setMontoPagado(parseFloat(e.target.value) || 0)}
                      min="0"
                      step="0.01"
                      placeholder="0.00"
                    />
                  </div>
                  {montoPagado > 0 && (
                    <div className="mt-2 p-2 bg-info bg-opacity-10 rounded">
                      <div className="d-flex justify-content-between">
                        <span>Cambio:</span>
                        <span className="fw-bold text-success">${calcularCambio().toFixed(2)}</span>
                      </div>
                    </div>
                  )}
                </div>
              )}

              {/* Botones de Acción */}
              <div className="d-grid gap-2">
                <button
                  className="btn btn-success btn-lg"
                  onClick={finalizarVenta}
                  disabled={carrito.length === 0 || loading}
                >
                  {loading ? (
                    <>
                      <span className="spinner-border spinner-border-sm me-2"></span>
                      Procesando...
                    </>
                  ) : (
                    <>
                      <i className="bi bi-check-circle-fill me-2"></i>
                      Finalizar Venta
                    </>
                  )}
                </button>

                <button
                  className="btn btn-outline-danger"
                  onClick={() => {
                    if (confirm('¿Deseas cancelar la venta y limpiar el carrito?')) {
                      setCarrito([]);
                      setDescuentoGlobal(0);
                      setMontoPagado(0);
                    }
                  }}
                  disabled={carrito.length === 0}
                >
                  <i className="bi bi-x-circle me-2"></i>
                  Cancelar Venta
                </button>
              </div>

              {/* Info adicional */}
              <div className="mt-4 p-3 bg-light rounded">
                <small className="text-muted">
                  <i className="bi bi-info-circle me-2"></i>
                  <strong>Vendedor:</strong> {user?.nombre || 'N/A'}<br />
                  <strong>Sucursal:</strong> {user?.sucursal_id || 'N/A'}
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
