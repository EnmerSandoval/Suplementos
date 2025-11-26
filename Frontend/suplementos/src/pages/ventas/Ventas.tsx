import { useState, useEffect } from 'react';
import Card from '../../components/common/Card';
import Button from '../../components/common/Button';
import type { Producto, DetalleVenta, Cliente } from '../../types';
import { productosService } from '../../api/productosService';
import { clientesService } from '../../api/clientesService';
import { ventasService } from '../../api/ventasService';
import { useAuth } from '../../context/AuthContext';

export default function Ventas() {
  const { user } = useAuth();
  const [productos, setProductos] = useState<Producto[]>([]);
  const [clientes, setClientes] = useState<Cliente[]>([]);
  const [cart, setCart] = useState<DetalleVenta[]>([]);
  const [selectedCliente, setSelectedCliente] = useState<number | undefined>();
  const [tipoPago, setTipoPago] = useState<'efectivo' | 'tarjeta' | 'credito' | 'mixto'>('efectivo');

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      const [productosRes, clientesRes] = await Promise.all([
        productosService.list(),
        clientesService.list(),
      ]);
      if (productosRes.success) setProductos(productosRes.data || []);
      if (clientesRes.success) setClientes(clientesRes.data || []);
    } catch (error) {
      console.error('Error al cargar datos:', error);
    }
  };

  const addToCart = (producto: Producto) => {
    const existing = cart.find(item => item.producto_id === producto.id);
    if (existing) {
      setCart(cart.map(item =>
        item.producto_id === producto.id
          ? { ...item, cantidad: item.cantidad + 1, subtotal: (item.cantidad + 1) * item.precio_unitario }
          : item
      ));
    } else {
      setCart([...cart, {
        producto_id: producto.id,
        cantidad: 1,
        precio_unitario: producto.precio_venta,
        descuento: 0,
        subtotal: producto.precio_venta,
      }]);
    }
  };

  const removeFromCart = (productoId: number) => {
    setCart(cart.filter(item => item.producto_id !== productoId));
  };

  const total = cart.reduce((sum, item) => sum + item.subtotal, 0);

  const handleVenta = async () => {
    try {
      if (cart.length === 0) {
        alert('Agregue productos al carrito');
        return;
      }

      const ventaData = {
        sucursal_id: user?.sucursal_id,
        usuario_id: user?.id,
        cliente_id: selectedCliente,
        tipo_pago: tipoPago,
        subtotal: total,
        descuento: 0,
        total,
        detalles: cart,
      };

      const response = await ventasService.create(ventaData);
      if (response.success) {
        alert('Venta registrada exitosamente');
        setCart([]);
        setSelectedCliente(undefined);
      }
    } catch (error) {
      console.error('Error al registrar venta:', error);
      alert('Error al registrar la venta');
    }
  };

  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 mb-6">Punto de Venta (POS)</h1>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2">
          <Card title="Productos">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {productos.slice(0, 9).map(producto => (
                <div
                  key={producto.id}
                  className="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow cursor-pointer"
                  onClick={() => addToCart(producto)}
                >
                  <h3 className="font-semibold text-gray-900">{producto.nombre}</h3>
                  <p className="text-sm text-gray-600">{producto.codigo}</p>
                  <p className="text-lg font-bold text-green-600 mt-2">
                    ${producto.precio_venta.toFixed(2)}
                  </p>
                </div>
              ))}
            </div>
          </Card>
        </div>

        <div>
          <Card title="Carrito de Compra">
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                <select
                  className="w-full px-3 py-2 border border-gray-300 rounded-md"
                  value={selectedCliente || ''}
                  onChange={(e) => setSelectedCliente(e.target.value ? Number(e.target.value) : undefined)}
                >
                  <option value="">Seleccionar cliente</option>
                  {clientes.map(cliente => (
                    <option key={cliente.id} value={cliente.id}>{cliente.nombre}</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Tipo de Pago</label>
                <select
                  className="w-full px-3 py-2 border border-gray-300 rounded-md"
                  value={tipoPago}
                  onChange={(e) => setTipoPago(e.target.value as any)}
                >
                  <option value="efectivo">Efectivo</option>
                  <option value="tarjeta">Tarjeta</option>
                  <option value="credito">Crédito</option>
                  <option value="mixto">Mixto</option>
                </select>
              </div>

              <div className="border-t pt-4">
                {cart.length === 0 ? (
                  <p className="text-gray-500 text-center">Carrito vacío</p>
                ) : (
                  <div className="space-y-2">
                    {cart.map((item, index) => {
                      const producto = productos.find(p => p.id === item.producto_id);
                      return (
                        <div key={index} className="flex justify-between items-center">
                          <div>
                            <p className="font-medium">{producto?.nombre}</p>
                            <p className="text-sm text-gray-600">Cant: {item.cantidad}</p>
                          </div>
                          <div className="text-right">
                            <p className="font-bold">${item.subtotal.toFixed(2)}</p>
                            <button
                              onClick={() => removeFromCart(item.producto_id)}
                              className="text-xs text-red-600 hover:underline"
                            >
                              Eliminar
                            </button>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>

              <div className="border-t pt-4">
                <div className="flex justify-between text-lg font-bold">
                  <span>Total:</span>
                  <span>${total.toFixed(2)}</span>
                </div>
              </div>

              <Button
                variant="primary"
                className="w-full"
                onClick={handleVenta}
                disabled={cart.length === 0}
              >
                Completar Venta
              </Button>
            </div>
          </Card>
        </div>
      </div>
    </div>
  );
}
