# Despliegue en Render (plan gratis)

Este repositorio trae todo lo necesario para publicar Venexpress en
[Render](https://render.com) sin costo:

| Recurso | Qué es | Plan |
| --- | --- | --- |
| `venexpress` | Backend Laravel en Docker: web + cola de correos + tareas programadas | Free web service |
| `venexpress-db` | Base de datos PostgreSQL | Free Postgres |
| `venexpress-repartidor` | App del repartidor en versión web (Flutter), desde el repo `venexpress_driver` | Static site (gratis) |

La definición está en [`render.yaml`](../render.yaml) (Blueprint) y la
imagen en [`Dockerfile`](../Dockerfile) + [`docker/`](../docker).

---

## Lo que hay que saber del plan gratis

- **Se duerme.** Si nadie entra en 15 minutos, Render apaga el servicio.
  La siguiente visita tarda cerca de un minuto en abrir. Mientras está
  dormido tampoco corren la cola de correos ni las tareas programadas
  (sincronizar la tasa BCV): siguen al despertar.
- **La base de datos gratis caduca a los 30 días.** Después hay 14 días
  para pasarla a un plan pago; si no, Render **la borra con todos los
  datos**. Para operar de verdad hay que pasarla a pago (o usar otra base
  de datos) antes de que caduque.
- **El disco del servidor no es permanente.** Todo lo que se sube (fotos
  de cédulas, comprobantes, evidencias de entrega, fotos de productos) se
  pierde en cada reinicio o despliegue, **salvo** que se configure un
  almacenamiento S3 (ver "Archivos permanentes" más abajo).
- **Sin correo por los puertos normales.** Render gratis bloquea los
  puertos 25, 465 y 587. Hay que usar un proveedor de correo que acepte
  el puerto **2525** (ver "Correo").
- **Sin consola.** No se puede entrar al servidor a correr comandos: el
  primer administrador se crea solo con `ADMIN_EMAIL` / `ADMIN_PASSWORD`,
  y las migraciones corren solas en cada arranque.
- 512 MB de RAM y 0,1 CPU. Probado con esos límites: arranca en ~25 s y
  usa ~140 MB en reposo.

---

## Pasos

### 1. Correo (antes de empezar)

Sin correo no llegan los códigos de verificación del registro ni el PIN
de entrega. Cualquier proveedor SMTP con puerto 2525 sirve; por ejemplo
[Brevo](https://www.brevo.com) (300 correos/día gratis):

1. Crea la cuenta y verifica el correo remitente (p. ej. `no-responder@tudominio.com`).
2. En *SMTP & API* copia el servidor (`smtp-relay.brevo.com`), el usuario
   y la clave SMTP.

### 2. Crear todo con el Blueprint

1. Entra a Render con tu cuenta de GitHub y dale acceso a los dos
   repositorios: `venexpress` y `venexpress_driver`.
2. **New → Blueprint** → elige el repositorio `venexpress` (rama `main`).
3. Render pide las variables marcadas como secretas:

   | Variable | Valor |
   | --- | --- |
   | `ADMIN_EMAIL` | correo del primer administrador |
   | `ADMIN_PASSWORD` | su contraseña (mínimo 10 caracteres) |
   | `MAIL_HOST` | p. ej. `smtp-relay.brevo.com` |
   | `MAIL_USERNAME` / `MAIL_PASSWORD` | usuario y clave SMTP |
   | `MAIL_FROM_ADDRESS` | el remitente verificado |
   | `API_BASE_URL` | déjala vacía por ahora (paso 3) |

4. **Apply**. El primer despliegue del backend tarda unos minutos
   (compila la imagen y crea las tablas).

### 3. Conectar la app del repartidor

1. Copia la URL del servicio `venexpress` (algo como
   `https://venexpress.onrender.com`).
2. En el sitio `venexpress-repartidor` → *Environment*, pon
   `API_BASE_URL` = esa URL + `/api`
   (p. ej. `https://venexpress.onrender.com/api`).
3. *Manual Deploy → Deploy latest commit*. El build instala Flutter y
   compila la app (unos minutos).

### 4. Primer ingreso y configuración

Entra a `https://<tu-backend>.onrender.com/admin/login` con
`ADMIN_EMAIL` / `ADMIN_PASSWORD` y configura, en este orden, lo mínimo
para poder registrar guías:

1. Almacenes y su **cobertura** (estado/ciudad que atiende cada uno).
2. Matriz de **tarifas** y distancias entre ciudades.
3. Tasa **BCV**: se sincroniza sola al arrancar y en horario hábil; si
   no aparece, revisa los logs.
4. Agencias aliadas (y las verificadas como destino de retiro), rutas y
   usuarios de taquilla/almacén.

Cambiar la contraseña del admin después es seguro: `ADMIN_PASSWORD` solo
se usa si el usuario todavía no existe.

---

## Archivos permanentes (recomendado)

Para no perder los archivos subidos, usa un almacenamiento compatible con
S3, por ejemplo Cloudflare R2 o Backblaze B2 (ambos con capa gratis).
Crea **dos buckets**:

- uno **privado** para documentos (cédulas, comprobantes, evidencias);
- uno **público** para fotos del marketplace y el APK del repartidor.

Y agrega en el servicio `venexpress` → *Environment*:

```
DOCUMENTS_DISK_DRIVER=s3
PUBLIC_DISK_DRIVER=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=auto            # R2: auto · B2: la región del bucket
AWS_ENDPOINT=https://...           # endpoint S3 del proveedor
AWS_USE_PATH_STYLE_ENDPOINT=true
AWS_BUCKET=venexpress-documentos   # el privado
PUBLIC_AWS_BUCKET=venexpress-publico
PUBLIC_DISK_URL=https://...        # URL pública del bucket público
```

El APK del repartidor se publica subiéndolo al bucket público como
`downloads/venexpress-repartidor.apk`; la página de descarga de la app lo
detecta sola.

---

## Opcional

- **Que no se duerma:** un monitor gratuito (p. ej. UptimeRobot) que
  visite `https://<tu-backend>.onrender.com/up` cada 10 minutos. Consume
  ~744 de las 750 horas gratis del mes, así que solo alcanza para **un**
  servicio así.
- **Login con Google:** `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` y
  `GOOGLE_REDIRECT_URI` (`https://<tu-backend>.onrender.com/auth/google/callback`).
- **Mapa / geocodificación:** `GOOGLE_MAPS_API_KEY`.

## App Android (APK)

La versión web sirve desde el navegador del teléfono. Para el APK,
compílalo apuntando al backend de Render:

```
flutter build apk --release --dart-define=API_BASE_URL=https://<tu-backend>.onrender.com/api
```

## Problemas comunes

- **El servicio no arranca:** *Logs* del servicio `venexpress`. Los
  mensajes del arranque empiezan con `[venexpress]`.
- **Los correos no llegan:** revisa `MAIL_*` y que el puerto sea 2525.
  Con `MAIL_MAILER=log` los correos solo se escriben en el log.
- **La app del repartidor no inicia sesión:** `API_BASE_URL` debe
  terminar en `/api` y usar `https://`; después de cambiarla hay que
  volver a desplegar el sitio.
- **Despliegues automáticos:** el backend se redespliega solo cuando los
  tests del CI de GitHub pasan en `main` (`autoDeployTrigger: checksPass`).

## Qué se probó

- Los 934 tests pasan en PostgreSQL 17 y en SQLite (en MySQL 8 todos
  menos uno del marketplace que ya fallaba ahí antes de estos cambios),
  y las 117 migraciones suben y se revierten completas.
- La imagen se probó con 512 MB y 0,1 CPU: arranque, migraciones,
  creación del admin, cola, scheduler, recorrido de todas las pantallas
  por rol sin errores, HTTPS detrás del proxy y almacenamiento S3.
- La app web del repartidor inicia sesión y consume la API (CORS).
