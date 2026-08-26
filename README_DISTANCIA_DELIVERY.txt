VENEXPRESS - CAMBIOS DISTANCIA, DESTINO Y DELIVERY

Archivos incluidos:
- Selector Estado -> Ciudad destino.
- Distancia por carretera mediante Nominatim + OSRM.
- La distancia se guarda en city_distances como respaldo.
- El precio usa la distancia calculada.
- Opcion "Requiere delivery".
- Campos de direccion exacta, sector/urbanizacion y referencia.
- Cargo de delivery configurable desde Admin > Tarifas.
- Se guarda distancia, estado destino y datos de delivery en cada paquete.

INSTALACION

1. Copia estos archivos sobre el proyecto venexpress respetando las carpetas.

2. Ejecuta:
   php artisan migrate

3. Ejecuta:
   php artisan optimize:clear

4. En Admin > Tarifas coloca el precio de:
   Delivery fijo (USD)

5. Prueba:
   /ally/pedidos/nuevo

APIS UTILIZADAS
- OpenStreetMap Nominatim: geocodificacion de ciudades.
- OSRM: calculo de distancia por carretera.

No se requiere API key para esta implementacion de desarrollo.
