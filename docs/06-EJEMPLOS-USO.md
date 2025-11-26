# EJEMPLOS DE USO DEL SISTEMA

Esta guía proporciona ejemplos prácticos de cómo usar el sistema para diferentes escenarios.

---

## 1. ESCENARIO: CONFIGURACIÓN INICIAL

### Paso 1: Crear Sucursales

**Como:** Administrador
**Objetivo:** Configurar las sucursales del negocio

#### Sucursal Normal (Tienda Propia)

**Endpoint:** `POST /api/v1/sucursales`

```json
{
  "codigo_sucursal": "SUC001",
  "nombre": "Tienda Central",
  "tipo_sucursal": "NORMAL",
  "direccion": "Zona 10, Ciudad de Guatemala",
  "telefono": "2222-3333",
  "email": "central@suplementos.com",
  "responsable": "María García"
}
```

#### Sucursal Convenio con Gimnasio

```json
{
  "codigo_sucursal": "GYM001",
  "nombre": "Gimnasio Power Fitness",
  "tipo_sucursal": "CONVENIO_GIMNASIO",
  "direccion": "Zona 15, Ciudad de Guatemala",
  "telefono": "3333-4444",
  "email": "powerfitness@gym.com",
  "responsable": "Carlos López"
}
```

### Paso 2: Crear Usuarios Vendedores

**Endpoint:** `POST /api/v1/usuarios`

```json
{
  "id_rol": 2,
  "id_sucursal_principal": 1,
  "nombre_completo": "Ana Martínez",
  "usuario": "ana.martinez",
  "contrasena": "vendedor123",
  "email": "ana@suplementos.com",
  "telefono": "5555-1234"
}
```

### Paso 3: Registrar Productos

**Endpoint:** `POST /api/v1/productos`

```json
{
  "codigo_producto": "PROT001",
  "nombre": "Proteína Whey Premium 2lb",
  "descripcion": "Proteína de suero de leche ultra filtrada, sabor chocolate",
  "categoria": "suplemento",
  "unidad_medida": "frascos",
  "precio_base": 350.00,
  "requiere_lote": 1
}
```

Más productos:

```json
{
  "codigo_producto": "BCAA001",
  "nombre": "BCAA 5000 - 60 tabletas",
  "descripcion": "Aminoácidos de cadena ramificada",
  "categoria": "suplemento",
  "unidad_medida": "frascos",
  "precio_base": 180.00,
  "requiere_lote": 1
}
```

```json
{
  "codigo_producto": "CREA001",
  "nombre": "Creatina Monohidratada 300g",
  "descripcion": "Creatina pura micronizada",
  "categoria": "suplemento",
  "unidad_medida": "frascos",
  "precio_base": 150.00,
  "requiere_lote": 1
}
```

### Paso 4: Registrar Lotes

**Endpoint:** `POST /api/v1/lotes` (este endpoint deberías agregarlo)

O al hacer entrada de inventario, crear el lote:

```json
{
  "id_producto": 1,
  "numero_lote": "LOT20250115",
  "fecha_fabricacion": "2025-01-15",
  "fecha_vencimiento": "2026-12-31"
}
```

### Paso 5: Cargar Inventario Inicial

**Endpoint:** `POST /api/v1/inventario/entrada`

```json
{
  "id_sucursal": 1,
  "id_producto": 1,
  "id_lote": 1,
  "cantidad": 50,
  "motivo": "Carga inicial de inventario"
}
```

---

## 2. ESCENARIO: VENTA DE CONTADO

### Caso: Cliente sin registro compra productos

**Paso 1:** Login del vendedor

```bash
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"usuario":"ana.martinez","contrasena":"vendedor123"}'
```

Respuesta:
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "usuario": { ... }
  }
}
```

**Paso 2:** Registrar la venta

**Endpoint:** `POST /api/v1/ventas`
**Headers:** `Authorization: Bearer {token}`

```json
{
  "id_sucursal": 1,
  "id_cliente": null,
  "tipo_venta": "CONTADO",
  "metodo_pago": "efectivo",
  "descuento": 0,
  "observaciones": "Venta de mostrador",
  "productos": [
    {
      "id_producto": 1,
      "id_lote": 1,
      "cantidad": 2,
      "precio_unitario": 350.00,
      "descuento": 0
    },
    {
      "id_producto": 2,
      "id_lote": 2,
      "cantidad": 1,
      "precio_unitario": 180.00,
      "descuento": 0
    }
  ]
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Venta creada exitosamente",
  "data": {
    "id_venta": 1,
    "numero_venta": "V-00000001",
    "total": 880.00,
    "fecha_venta": "2025-01-26 10:30:00",
    ...
  }
}
```

**Resultado:**
- ✅ Venta registrada
- ✅ Inventario actualizado automáticamente
- ✅ Movimientos de stock generados

---

## 3. ESCENARIO: VENTA A CRÉDITO

### Caso: Cliente frecuente compra a crédito

**Paso 1:** Registrar cliente

**Endpoint:** `POST /api/v1/clientes`

```json
{
  "nombre_completo": "Juan Carlos Pérez",
  "identificacion": "1234567890101",
  "telefono": "5555-6666",
  "email": "juan@email.com",
  "direccion": "Zona 5, Ciudad de Guatemala",
  "limite_credito": 5000.00
}
```

**Paso 2:** Crear venta a crédito

**Endpoint:** `POST /api/v1/ventas`

```json
{
  "id_sucursal": 1,
  "id_cliente": 1,
  "tipo_venta": "CREDITO",
  "metodo_pago": "credito",
  "descuento": 50.00,
  "observaciones": "Cliente frecuente - 30 días de crédito",
  "fecha_limite_pago": "2025-02-26",
  "productos": [
    {
      "id_producto": 1,
      "id_lote": 1,
      "cantidad": 5,
      "precio_unitario": 350.00,
      "descuento": 0
    }
  ]
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Venta creada exitosamente",
  "data": {
    "id_venta": 2,
    "numero_venta": "V-00000002",
    "total": 1700.00,
    "tipo_venta": "CREDITO",
    "credito": {
      "id_credito": 1,
      "monto_total": 1700.00,
      "saldo_pendiente": 1700.00,
      "fecha_limite_pago": "2025-02-26"
    }
  }
}
```

**Paso 3:** Cliente hace un pago parcial

**Endpoint:** `POST /api/v1/creditos/1/pagar`

```json
{
  "monto_pago": 1000.00,
  "metodo_pago": "efectivo",
  "observaciones": "Abono parcial"
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Pago registrado exitosamente",
  "data": {
    "id_pago": 1,
    "credito": {
      "saldo_pendiente": 700.00,
      "estado": "PAGADO_PARCIAL"
    }
  }
}
```

**Paso 4:** Cliente salda completamente

**Endpoint:** `POST /api/v1/creditos/1/pagar`

```json
{
  "monto_pago": 700.00,
  "metodo_pago": "transferencia",
  "observaciones": "Pago final del crédito"
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Pago registrado exitosamente",
  "data": {
    "credito": {
      "saldo_pendiente": 0.00,
      "estado": "PAGADO"
    }
  }
}
```

---

## 4. ESCENARIO: COTIZACIÓN Y CONVERSIÓN A VENTA

### Caso: Cliente solicita cotización antes de comprar

**Paso 1:** Crear cotización

**Endpoint:** `POST /api/v1/cotizaciones`

```json
{
  "id_cliente": 1,
  "id_sucursal": 1,
  "fecha_vencimiento": "2025-02-10",
  "descuento": 100.00,
  "observaciones": "Cotización para pedido mensual",
  "productos": [
    {
      "id_producto": 1,
      "cantidad": 10,
      "precio_unitario": 350.00,
      "descuento": 0
    },
    {
      "id_producto": 2,
      "cantidad": 5,
      "precio_unitario": 180.00,
      "descuento": 0
    }
  ]
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Cotización creada exitosamente",
  "data": {
    "id_cotizacion": 1,
    "numero_cotizacion": "COT-00000001",
    "total": 3800.00,
    "estado": "PENDIENTE",
    ...
  }
}
```

**Paso 2:** Cliente acepta, actualizar estado

**Endpoint:** `PUT /api/v1/cotizaciones/1`

```json
{
  "estado": "ACEPTADA",
  "observaciones": "Cliente confirmó pedido vía teléfono"
}
```

**Paso 3:** Convertir cotización a venta

**Endpoint:** `POST /api/v1/cotizaciones/1/convertir`

```json
{
  "tipo_venta": "CREDITO",
  "metodo_pago": "credito",
  "fecha_limite_pago": "2025-03-10"
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Cotización convertida a venta exitosamente",
  "data": {
    "id_venta": 3,
    "numero_venta": "V-00000003",
    "cotizacion": {
      "estado": "CONVERTIDA_VENTA",
      "id_venta_generada": 3
    }
  }
}
```

---

## 5. ESCENARIO: TRASLADO ENTRE SUCURSALES

### Caso: Gimnasio necesita productos que están en otra sucursal

**Paso 1:** Verificar inventario de destino

**Endpoint:** `GET /api/v1/inventario/sucursal/2`

**Paso 2:** Realizar traslado

**Endpoint:** `POST /api/v1/inventario/traslado`

```json
{
  "id_sucursal_origen": 1,
  "id_sucursal_destino": 2,
  "id_producto": 1,
  "id_lote": 1,
  "cantidad": 10,
  "motivo": "Traslado por alta demanda en gimnasio"
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Traslado realizado exitosamente",
  "data": {
    "movimiento": {
      "id_movimiento": 15,
      "tipo_movimiento": "TRASLADO",
      "cantidad": 10,
      "sucursal_origen": "Tienda Central",
      "sucursal_destino": "Gimnasio Power Fitness"
    },
    "inventario_origen": {
      "cantidad_disponible": 40
    },
    "inventario_destino": {
      "cantidad_disponible": 10
    }
  }
}
```

---

## 6. ESCENARIO: CIERRE DE CAJA DIARIO

### Caso: Vendedor cierra caja al finalizar el día

**Paso 1:** Crear cierre de caja

**Endpoint:** `POST /api/v1/cierres-caja`

```json
{
  "id_sucursal": 1,
  "fecha_cierre": "2025-01-26"
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Cierre de caja creado",
  "data": {
    "id_cierre": 1,
    "total_ventas": 5500.00,
    "total_efectivo": 3200.00,
    "total_tarjeta": 2000.00,
    "total_otros": 300.00,
    "estado": "ABIERTO"
  }
}
```

**Paso 2:** Cerrar caja con monto declarado

**Endpoint:** `PUT /api/v1/cierres-caja/1/cerrar`

```json
{
  "monto_declarado": 5480.00,
  "observaciones": "Diferencia de Q20 por cambio dado incorrectamente"
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Caja cerrada exitosamente",
  "data": {
    "id_cierre": 1,
    "total_ventas": 5500.00,
    "monto_declarado": 5480.00,
    "diferencia": -20.00,
    "estado": "CERRADO"
  }
}
```

---

## 7. ESCENARIO: REPORTES

### Reporte de Inventario por Sucursal

**Endpoint:** `GET /api/v1/reportes/inventario?id_sucursal=1`

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "producto": "Proteína Whey Premium 2lb",
      "codigo_producto": "PROT001",
      "categoria": "suplemento",
      "numero_lote": "LOT20250115",
      "fecha_vencimiento": "2026-12-31",
      "cantidad_disponible": 40,
      "cantidad_minima": 10,
      "alerta_inventario": "NORMAL"
    },
    {
      "producto": "BCAA 5000",
      "codigo_producto": "BCAA001",
      "numero_lote": "LOT20250110",
      "fecha_vencimiento": "2025-03-15",
      "cantidad_disponible": 5,
      "cantidad_minima": 10,
      "alerta_inventario": "PROXIMO_A_VENCER"
    }
  ]
}
```

### Reporte de Productos por Vencer (30 días)

**Endpoint:** `GET /api/v1/reportes/productos-por-vencer?dias=30&id_sucursal=1`

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "producto": "BCAA 5000",
      "numero_lote": "LOT20250110",
      "fecha_vencimiento": "2025-03-15",
      "dias_para_vencer": 48,
      "cantidad_disponible": 5,
      "sucursal": "Tienda Central"
    }
  ]
}
```

### Reporte de Ventas por Período

**Endpoint:** `GET /api/v1/reportes/ventas?id_sucursal=1&fecha_inicio=2025-01-01&fecha_fin=2025-01-31`

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "resumen": {
      "total_ventas": 15,
      "total_contado": 10,
      "total_credito": 5,
      "monto_total": 25500.00,
      "monto_contado": 15000.00,
      "monto_credito": 10500.00
    },
    "ventas": [
      {
        "numero_venta": "V-00000001",
        "fecha_venta": "2025-01-26",
        "cliente": "Cliente genérico",
        "tipo_venta": "CONTADO",
        "total": 880.00
      },
      // ... más ventas
    ],
    "productos_mas_vendidos": [
      {
        "producto": "Proteína Whey Premium 2lb",
        "cantidad_vendida": 50,
        "monto_total": 17500.00
      }
    ]
  }
}
```

### Reporte de Créditos Pendientes

**Endpoint:** `GET /api/v1/reportes/creditos-cliente?estado=PENDIENTE`

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "cliente": "Juan Carlos Pérez",
      "identificacion": "1234567890101",
      "numero_creditos": 2,
      "monto_total_creditos": 5000.00,
      "saldo_pendiente": 3200.00,
      "creditos_vencidos": 0,
      "creditos_por_vencer": 1
    }
  ]
}
```

### Reporte Mensual de Convenio con Gimnasio

**Endpoint:** `GET /api/v1/reportes/convenios-mensuales?mes=2025-01&id_sucursal=2`

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "sucursal": "Gimnasio Power Fitness",
    "tipo_sucursal": "CONVENIO_GIMNASIO",
    "mes": "2025-01",
    "total_ventas": 25,
    "monto_total": 18500.00,
    "productos_vendidos": [
      {
        "producto": "Proteína Whey Premium 2lb",
        "cantidad": 30,
        "monto": 10500.00
      },
      {
        "producto": "BCAA 5000",
        "cantidad": 25,
        "monto": 4500.00
      }
    ],
    "metodos_pago": {
      "efectivo": 12000.00,
      "tarjeta": 6500.00
    }
  }
}
```

---

## 8. ESCENARIO: AJUSTE DE INVENTARIO

### Caso: Conteo físico difiere del sistema

**Endpoint:** `POST /api/v1/inventario/ajuste`

```json
{
  "id_sucursal": 1,
  "id_producto": 1,
  "id_lote": 1,
  "cantidad": -3,
  "motivo": "Ajuste por conteo físico - producto dañado",
  "observaciones": "3 frascos encontrados dañados durante inventario"
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Ajuste de inventario realizado",
  "data": {
    "inventario_anterior": 40,
    "ajuste": -3,
    "inventario_nuevo": 37
  }
}
```

---

## 9. ESCENARIO: PRECIOS ESPECÍFICOS POR SUCURSAL

### Caso: Gimnasio tiene precio especial

**Endpoint:** `POST /api/v1/precios-sucursal` (agregar este endpoint)

```json
{
  "id_producto": 1,
  "id_sucursal": 2,
  "precio_venta": 320.00,
  "fecha_vigencia_inicio": "2025-01-01",
  "fecha_vigencia_fin": null
}
```

Ahora cuando se venda en el gimnasio (sucursal 2), el precio será Q320 en lugar de Q350.

---

## 10. FLUJO COMPLETO: DÍA TÍPICO DE UN VENDEDOR

### 1. Iniciar sesión (8:00 AM)
```bash
POST /api/v1/auth/login
```

### 2. Consultar inventario disponible (8:05 AM)
```bash
GET /api/v1/inventario/sucursal/1
```

### 3. Primera venta del día (9:15 AM)
```bash
POST /api/v1/ventas
```

### 4. Cliente solicita cotización (10:30 AM)
```bash
POST /api/v1/cotizaciones
```

### 5. Registrar cliente nuevo (11:00 AM)
```bash
POST /api/v1/clientes
```

### 6. Venta a crédito (11:30 AM)
```bash
POST /api/v1/ventas
```

### 7. Cliente abona a su crédito (14:00 PM)
```bash
POST /api/v1/creditos/5/pagar
```

### 8. Más ventas durante el día
```bash
POST /api/v1/ventas (varias veces)
```

### 9. Cierre de caja (18:00 PM)
```bash
POST /api/v1/cierres-caja
PUT /api/v1/cierres-caja/1/cerrar
```

### 10. Cerrar sesión (18:15 PM)
```bash
POST /api/v1/auth/logout
```

---

## NOTAS IMPORTANTES

1. **Tokens JWT**: Guardar el token después del login y enviarlo en todas las peticiones subsecuentes
2. **Formato de fechas**: Usar `YYYY-MM-DD` para fechas y `YYYY-MM-DD HH:MM:SS` para timestamps
3. **IDs**: Todos los IDs son numéricos enteros
4. **Decimales**: Usar punto (.) como separador decimal, no coma
5. **Headers**: Siempre incluir `Content-Type: application/json` en peticiones POST/PUT

---

Estos ejemplos cubren los casos de uso más comunes del sistema. Para casos específicos no cubiertos, consultar la documentación de endpoints en `docs/02-API-ENDPOINTS.md`.
