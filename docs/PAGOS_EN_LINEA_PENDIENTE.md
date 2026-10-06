# Pendiente: pago en línea del contra entrega (COD) por el cliente

Estado: **pendiente** (la parte de pagos en línea todavía no está lista).
Se dejó documentado en la Fase 2 de entregas a domicilio.

## Qué ya funciona (Fase 2)

- Un paquete con cobro contra entrega (`is_cod`) **no se puede entregar
  con el pago pendiente**: al confirmar la entrega el repartidor debe
  registrar el cobro (`PackageService::completeDelivery()`):
  - forma de pago (`cod_payment_method`, ver `Package::PAYMENT_METHODS`);
  - si el pago es electrónico (pago móvil, transferencia, Zelle), el
    número de referencia es obligatorio (`cod_payment_reference`,
    `Package::PAYMENT_METHODS_REQUIRING_REFERENCE`);
  - el comprobante (foto) es opcional (`cod_payment_proof_path`, disco
    privado `documents`, carpeta `cod-payment-proofs/`).
- Si el cliente le pagó en efectivo al repartidor, basta con indicar
  "Efectivo (USD)" o "Efectivo (VES)".
- Esto está en el panel web del repartidor (`Driver\PackageDetail`) y en
  la API de la app (`POST /api/driver/packages/{id}/complete-delivery`,
  campos `cod_payment_method`, `cod_payment_reference`,
  `cod_payment_proof`).
- Si el COD ya figura cobrado (`cod_collected_at` no es nulo), el
  repartidor no tiene que registrar nada y se le indica que ya está
  pagado.

## Qué falta

1. **El cliente paga desde la página antes de recibir el paquete.**
   - Pantalla: `App\Livewire\Client\PendingPayments` (los botones
     "Pago móvil", "Inmediato" y "Pagar todo" están deshabilitados a
     propósito; ver el comentario de esa clase).
   - Reutilizar `App\Models\PaymentOrder` con
     `purpose = PaymentOrder::PURPOSE_COD`, `payer_type = Customer` y
     `package_id` del paquete.
   - Confirmar la orden con el flujo que ya existe
     (`PaymentReconciliationService::confirm()` / webhook del banco).
     Al confirmarse, `markCodAsCollected()` ya marca el paquete como
     cobrado (`cod_status = liquidado`, `cod_collected_at`).
2. **Avisar al repartidor que ya está pagado.**
   - La app y el panel ya leen `cod_collected_at`: con eso el repartidor
     no pide el pago. Falta un aviso activo (notificación push o
     refresco del detalle) cuando el pago se confirma mientras el
     paquete está `EN_RUTA`.
3. **Revisión de los cobros registrados por el repartidor.**
   - Hoy la referencia y el comprobante quedan guardados en el paquete,
     pero nadie los concilia. Falta una pantalla para Admin que liste
     los COD cobrados por pago móvil/transferencia con su referencia y
     comprobante, y permita marcarlos como verificados/liquidados
     (reutilizando `PackageService::liquidateCod()`).

## Archivos relacionados

- `app/Services/PackageService.php` (`completeDelivery`, `collectCod`,
  `liquidateCod`)
- `app/Services/PaymentReconciliationService.php`
- `app/Livewire/Client/PendingPayments.php`
- `app/Livewire/Driver/PackageDetail.php` y
  `resources/views/livewire/driver/package-detail.blade.php`
- `app/Http/Controllers/Api/DriverPackageController.php`
- App Flutter (`venexpress_driver`): `lib/screens/package_detail_screen.dart`
