# Actualización automática de tasa BCV

Venexpress usa el endpoint oficial de DolarAPI para Venezuela:

`https://ve.dolarapi.com/v1/dolares/oficial`

DolarAPI documenta que la fuente del Dólar Oficial de Venezuela es el BCV.

## Funcionamiento

1. Laravel ejecuta `bcv:sync` cada 15 minutos, de lunes a viernes entre
   1:30pm y 6:30pm (hora de Venezuela), que es cuando el BCV publica
   (ver `routes/console.php`).
2. El comando consulta la API.
3. Si la tasa es igual a la última guardada, no crea nada.
4. Si cambió, guarda una nueva tasa con fecha/hora. Si el cambio supera
   `BCV_MAX_RATE_CHANGE_PERCENT` (30% por defecto) NO la guarda: avisa a
   los admins por correo para que la verifiquen y la carguen a mano.
5. Esto permite conservar las dos tasas publicadas durante el mismo día.
6. Cada paquete sigue guardando `bcv_rate_used`, por lo que un envío histórico no cambia si la tasa cambia después.

## En desarrollo local

Deja corriendo:

`php artisan schedule:work`

En producción, configura el scheduler de Laravel para ejecutar `php artisan schedule:run` cada minuto.

## Prueba manual

`php artisan bcv:sync`

## URL configurable

En `.env`:

`BCV_API_URL=https://ve.dolarapi.com/v1/dolares/oficial`
