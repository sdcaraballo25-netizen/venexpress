VENEXPRESS - FASE 3

Reemplaza estos archivos conservando exactamente sus rutas:
- app/Services/DistanceApiService.php
- app/Livewire/Admin/Dashboard.php
- app/Livewire/Admin/RoutesDashboard.php

Luego ejecuta:
php artisan optimize:clear
php artisan migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache

La Fase 3 mejora la cache de distancias, evita consultas repetidas en el dashboard y reduce consultas duplicadas del dashboard de rutas.
