<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\PackageCreate;
use App\Models\BcvRate;
use App\Models\CityDistance;
use App\Models\Customer;
use App\Models\Package;
use App\Models\RateMatrix;
use App\Notifications\PackageCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Un remitente/destinatario que no estaba registrado por cédula debe
 * dar su correo obligatoriamente al registrar un pedido, porque a ese
 * correo se le envían los datos de la guía apenas se crea (avisando a
 * cada uno con un mensaje distinto: "tu paquete fue enviado..." /
 * "fulano te ha enviado un paquete...").
 */
class PackageCreateEmailNotificationTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();

        RateMatrix::create([
            'base_price_usd' => 2.00,
            'price_per_kg_usd' => 0.50,
            'price_per_km_usd' => 0.01,
            'envelope_price_usd' => 1.50,
            'fragile_surcharge_usd' => 3.00,
            'insurance_percentage' => 2.00,
            'delivery_price_usd' => 4.00,
        ]);

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => now()->toDateString(),
            'effective_at' => now(),
            'source' => 'manual',
        ]);

        CityDistance::setDistance(
            cityOne: 'Caracas',
            stateOne: 'Distrito Capital',
            cityTwo: 'Valencia',
            stateTwo: 'Carabobo',
            distanceKm: 150,
        );
    }

    private function fillRequiredFields($component)
    {
        $pickupAlly = $this->createAlly([
            'business_name' => 'Punto de Retiro Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => true,
        ]);

        return $component
            ->set('sender_doc_number', '12345678')
            ->set('sender_name', 'Juan Pérez')
            ->set('sender_phone', '0414-1234567')
            ->set('recipient_doc_number', '87654321')
            ->set('recipient_name', 'María Gómez')
            ->set('recipient_phone', '0424-7654321')
            ->set('destination_state', 'Carabobo')
            ->set('destination_city', 'Valencia')
            ->set('physical_weight_kg', 2.0)
            ->set('payment_method', 'efectivo_usd')
            ->set('pickup_mode', Package::PICKUP_MODE_ALLY)
            ->set('pickup_ally_id', $pickupAlly->id);
    }

    public function test_sender_email_is_required_for_a_new_sender(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $component = $this->fillRequiredFields(Livewire::actingAs($ally->user)->test(PackageCreate::class))
            ->set('recipient_email', 'maria.gomez@example.com');

        $component->call('save')->assertHasErrors(['sender_email' => 'required']);
    }

    public function test_recipient_email_is_required_for_a_new_recipient(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $component = $this->fillRequiredFields(Livewire::actingAs($ally->user)->test(PackageCreate::class))
            ->set('sender_email', 'juan.perez@example.com');

        $component->call('save')->assertHasErrors(['recipient_email' => 'required']);
    }

    public function test_email_is_not_required_for_an_already_known_customer(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        Customer::create([
            'id_doc' => 'V-12345678',
            'name' => 'Juan Pérez',
            'phone' => '0414-1234567',
            'email' => 'juan.perez@example.com',
        ]);

        $component = $this->fillRequiredFields(Livewire::actingAs($ally->user)->test(PackageCreate::class))
            ->set('recipient_email', 'maria.gomez@example.com');

        $component->call('save')->assertHasNoErrors();
    }

    public function test_both_sender_and_recipient_are_notified_by_email_when_the_package_is_created(): void
    {
        Notification::fake();

        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $component = $this->fillRequiredFields(Livewire::actingAs($ally->user)->test(PackageCreate::class))
            ->set('sender_email', 'juan.perez@example.com')
            ->set('recipient_email', 'maria.gomez@example.com');

        $component->call('save')->assertHasNoErrors();

        Notification::assertSentOnDemand(
            PackageCreated::class,
            function (PackageCreated $notification, array $channels, object $notifiable) {
                return $notifiable->routes['mail'] === 'juan.perez@example.com';
            }
        );

        Notification::assertSentOnDemand(
            PackageCreated::class,
            function (PackageCreated $notification, array $channels, object $notifiable) {
                return $notifiable->routes['mail'] === 'maria.gomez@example.com';
            }
        );
    }
}
