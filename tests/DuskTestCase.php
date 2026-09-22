<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            // Igual que CHROME_BINARY: permite un chromedriver distinto al
            // que `php artisan dusk:chrome-driver` descarga por defecto.
            if ($driverPath = env('CHROME_DRIVER_BINARY')) {
                static::useChromedriver($driverPath);
            }

            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            // Necesario quien corra los tests como root (contenedores CI,
            // algunos entornos sandboxed) — Chrome se niega a arrancar con
            // el sandbox propio activado bajo ese usuario.
            '--no-sandbox',
            '--disable-dev-shm-usage',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        // Permite apuntar a un binario de Chrome/Chromium distinto al que
        // Dusk detecta por defecto (útil en entornos sandboxed sin acceso
        // al Chrome del sistema). No afecta a CI ni a un entorno normal,
        // donde CHROME_BINARY simplemente no está definido.
        if ($binary = env('CHROME_BINARY')) {
            $options->setBinary($binary);
        }

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
