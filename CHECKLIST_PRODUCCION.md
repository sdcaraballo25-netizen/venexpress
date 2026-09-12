# Checklist final antes de operar en real — Venexpress

Cruce de todo lo tocado en las Fases 1-4. Márcalo según lo vayas resolviendo.

## Backend (Laravel)

- [ ] `php artisan venexpress:check-production` sin errores (⚠ los warnings
      son aceptables si sabes por qué, pero revísalos uno por uno).
- [ ] `APP_ENV=production`, `APP_DEBUG=false` en el `.env` real del servidor.
- [ ] `APP_KEY` generado en el servidor y **respaldado en un lugar seguro**
      (rotarlo invalida todos los hashes de seguridad de las guías ya
      impresas — ver `Package::computeSecurityHash`).
- [ ] `MAIL_MAILER` apuntando a un proveedor real (no `log`).
- [ ] Worker de colas corriendo bajo Supervisor
      (`deploy/supervisor-venexpress-worker.conf`) — sin esto, los correos
      se quedan encolados para siempre.
- [ ] Cron del scheduler configurado (`* * * * * php artisan schedule:run`)
      — sin esto, `bcv:sync` nunca corre solo.
- [ ] Migraciones corridas en el servidor real, incluyendo
      `2026_09_12_000001_drop_ally_users_table` (Fase 2).
- [ ] `AllyUser.php` y `AllyUserService.php` eliminados del repo (Fase 2).
- [ ] HTTPS real activo en el dominio de producción (Let's Encrypt o
      similar) — la app del repartidor en release bloquea `http://` a
      propósito (Fase 1).
- [ ] Decisión tomada sobre el flujo de confirmación de pagos (pendiente,
      Fase 3 — hoy es manual/de prueba).

## App del repartidor (Flutter)

- [ ] `lib/config/api_config.dart`: `_prodBaseUrl` apunta al dominio real
      con `https://` (Fase 1).
- [ ] `AndroidManifest.xml` con permisos de INTERNET y CÁMARA (Fase 1, ya
      aplicado).
- [ ] `applicationId` propio en `build.gradle.kts` (ya cambiado a
      `com.venexpress.driver` — puedes ajustarlo, pero no lo dejes en
      `com.example.*`).
- [ ] Keystore de release generado y `android/key.properties` creado a
      partir de `key.properties.example` (Fase 4) — **guarda el .jks y las
      contraseñas fuera del proyecto, en un lugar seguro**. Si se pierde,
      no podrás actualizar la misma app nunca más.
- [ ] Ícono personalizado generado (`assets/icon/icon.png` +
      `dart run flutter_launcher_icons`) en vez del ícono por defecto de
      Flutter.
- [ ] `flutter build apk --release` genera un APK sin errores, usando ya
      el keystore real (verifica que no caiga de vuelta a la firma de
      debug — el build avisa cuál usó).
- [ ] Probado en un teléfono físico real (no solo emulador): login, escaneo
      con la cámara, y conexión a la API en producción.

## QA (Fase 4)

- [ ] `php artisan test` corre en verde, incluyendo los tests nuevos:
      `tests/Feature/Driver/DriverApiFlowTest.php` y
      `tests/Feature/PackageSecurityHashTest.php`.
      **Importante: estos tests fueron escritos revisando el código fuente
      real (controllers, servicios, modelos, migraciones), pero no pude
      ejecutarlos yo mismo — este entorno no tiene PHP instalado.
      Ejecútalos tú antes de confiar en ellos, y si algo falla, pégame el
      error exacto y lo corrijo.**
- [ ] Huecos de cobertura que quedan pendientes, sin test todavía (no
      bloqueantes para operar, pero recomendable cubrirlos con el tiempo):
      - Creación de paquete completa vía el formulario Livewire del
        aliado (`Ally/CreatePackage`), de punta a punta.
      - Asignación de rutas y paradas desde el panel admin
        (`RoutesManager`).
      - `PaymentReconciliationService` / confirmación de pagos — depende
        de qué decidas en la Fase 3.
      - Cálculo y liquidación de `DriverPaymentService` /
        `driver_remuneration_rates`.
