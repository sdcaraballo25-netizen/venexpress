---
name: venexpress-testing
description: Testing y verificación para Venexpress. Usar después de cambios en Laravel, Livewire, Blade, rutas, autenticación, base de datos, lógica de paquetes, repartidores, aliados, tarifas, QR y dashboards.
---

# Venexpress Testing

## Objetivo

Garantizar que los cambios realizados en Venexpress funcionen realmente y no se consideren terminados únicamente porque el código "parece correcto".

La regla principal es:

> modificar → probar → verificar → corregir → informar

Nunca afirmar que una funcionalidad está terminada sin haber realizado una verificación razonable.

---

# 1. Principio general

Cada cambio debe verificarse según su alcance.

### Cambio pequeño

Ejemplos:

- modificar un Blade
- cambiar un botón
- corregir una condición
- modificar un texto
- ajustar estilos

Verificar como mínimo:

- sintaxis
- compilación/build si aplica
- renderizado de la vista
- ausencia de errores obvios

### Cambio de backend

Ejemplos:

- Livewire
- controlador
- servicio
- modelo
- rutas
- middleware
- autenticación

Verificar:

- sintaxis PHP
- rutas
- lógica afectada
- permisos/roles
- errores Laravel
- comportamiento relacionado

### Cambio de base de datos

Verificar:

- migraciones
- relaciones
- nombres de columnas
- modelos
- datos existentes
- compatibilidad con código actual

### Cambio de flujo completo

Ejemplos:

- registrar paquete
- escanear guía
- asignar repartidor
- actualizar estado
- crear ruta
- calcular tarifa
- cerrar caja

Verificar el flujo completo, no solamente el archivo modificado.

---

# 2. Laravel

Antes de considerar terminado un cambio importante, utilizar cuando corresponda:

```bash
php artisan route:list