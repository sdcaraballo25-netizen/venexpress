# Checklist de infraestructura para producción

Estos dos puntos NO los puede validar `php artisan venexpress:check-production`
porque son procesos del sistema operativo, no configuración de Laravel.
Verifícalos manualmente al desplegar.

## 1. Cron del scheduler (necesario para `bcv:sync`)

`routes/console.php` programa `bcv:sync` cada hora, pero Laravel no ejecuta
nada solo — necesita que el servidor llame a `schedule:run` cada minuto.

En el servidor, edita el crontab del usuario que corre la app:

```
crontab -e
```

Y agrega:

```
* * * * * cd /var/www/venexpress && php artisan schedule:run >> /dev/null 2>&1
```

Verificación: corre `php artisan venexpress:check-production` un rato después
del deploy — si la tasa BCV tiene más de 6 horas sin actualizarse, te avisa
ahí mismo.

## 2. Worker de colas (necesario para que los correos se envíen)

Ver `deploy/supervisor-venexpress-worker.conf` — instrucciones de instalación
dentro del archivo. Sin este proceso corriendo, las notificaciones
(`PackageStatusUpdated`, verificación de correo) se quedan encoladas para
siempre y nunca llegan al cliente, aunque el `MAIL_MAILER` esté bien
configurado.

## 3. Correo real (además de lo anterior)

`.env.example` trae `MAIL_MAILER=log`. En producción, configura un proveedor
real (Mailgun, Amazon SES, Postmark, etc.) en tu `.env` del servidor:

```
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notificaciones@tu-dominio.com
MAIL_FROM_NAME="Venexpress"
```

`php artisan venexpress:check-production` te avisará si esto sigue en `log`.
