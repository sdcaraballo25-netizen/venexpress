<?php

namespace Database\Seeders;

use App\Models\Ally;
use App\Models\BcvRate;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\RateMatrix;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Contraseña compartida por todos los usuarios de prueba.
     * Solo para entorno local/demo: nunca usar en producción.
     */
    protected const DEMO_PASSWORD = 'password';

    /**
     * Crea los datos mínimos para poder operar el sistema completo
     * en local/demo: un usuario por rol, un aliado activo, una
     * tarifa global y una tasa BCV vigente.
     *
     * Sin esto, un desarrollador nuevo no puede iniciar sesión en
     * ningún panel ni registrar una guía (TariffService::findRate()
     * lanza una excepción si no existe ningún RateMatrix).
     */
    public function run(): void
    {
        $this->seedAdmins();
        $this->seedAllyWithStaff();
        $this->seedDriver();
        $this->seedClient();
        $this->seedRateMatrix();
        $this->seedBcvRate();
    }

    protected function seedAdmins(): void
    {
        User::factory()->create([
            'name' => 'Admin Principal',
            'email' => 'admin@venexpress.test',
            'password' => Hash::make(self::DEMO_PASSWORD),
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Admin Operativo',
            'email' => 'operaciones@venexpress.test',
            'password' => Hash::make(self::DEMO_PASSWORD),
            'role' => User::ROLE_ADMIN_OPERATIVO,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    protected function seedAllyWithStaff(): void
    {
        $owner = User::factory()->create([
            'name' => 'Aliado Demo Caracas',
            'email' => 'aliado@venexpress.test',
            'password' => Hash::make(self::DEMO_PASSWORD),
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $ally = Ally::create([
            'user_id' => $owner->id,
            'business_name' => 'Agencia Demo Caracas',
            'rif' => 'J-00000000-0',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Francisco de Miranda, Caracas',
            'latitude' => 10.4880,
            'longitude' => -66.8791,
            'commission_percentage' => 10.00,
            // Activo directamente: en producción, un aliado nuevo
            // nace PENDIENTE y un admin lo aprueba desde /admin/allies.
            'status' => Ally::STATUS_ACTIVE,
        ]);

        User::factory()->create([
            'name' => 'Taquilla Demo Caracas',
            'email' => 'taquilla@venexpress.test',
            'password' => Hash::make(self::DEMO_PASSWORD),
            'role' => User::ROLE_ALIADO_TAQUILLA,
            'ally_id' => $ally->id,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    protected function seedDriver(): void
    {
        $driverUser = User::factory()->create([
            'name' => 'Repartidor Demo',
            'email' => 'repartidor@venexpress.test',
            'password' => Hash::make(self::DEMO_PASSWORD),
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        Driver::create([
            'user_id' => $driverUser->id,
            'vehicle_plate' => 'AB123CD',
            'vehicle_type' => 'Moto',
            'phone' => '+58 412 0000000',
            'status' => Driver::STATUS_ACTIVE,
        ]);
    }

    protected function seedClient(): void
    {
        $clientUser = User::factory()->create([
            'name' => 'Cliente Demo',
            'email' => 'cliente@venexpress.test',
            'password' => Hash::make(self::DEMO_PASSWORD),
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        // Sin este registro en customers, el panel de Cliente
        // (App\Livewire\Client\Dashboard) no encuentra ninguna guía,
        // aunque el destinatario de un paquete tenga esta cédula.
        Customer::updateOrCreate(
            ['id_doc' => 'V-00000000'],
            [
                'name' => $clientUser->name,
                'phone' => '+58 412 1111111',
                'email' => $clientUser->email,
            ]
        );
    }

    protected function seedRateMatrix(): void
    {
        if (RateMatrix::current()) {
            return;
        }

        RateMatrix::create([
            'base_price_usd' => 2.00,
            'price_per_kg_usd' => 0.50,
            'price_per_km_usd' => 0.02,
            'envelope_price_usd' => 3.00,
            'fragile_surcharge_usd' => 1.50,
            'insurance_percentage' => 3.50,
            'delivery_price_usd' => 2.50,
        ]);
    }

    protected function seedBcvRate(): void
    {
        if (BcvRate::current()) {
            return;
        }

        BcvRate::create([
            'rate' => 40.000000,
            'effective_date' => now()->toDateString(),
            'effective_at' => now(),
            'source' => 'seeder',
        ]);
    }
}
