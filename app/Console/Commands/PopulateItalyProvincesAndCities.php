<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateItalyProvincesAndCities extends Command
{
    protected $signature = 'populate:italy-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Italy provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Italy provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Italy country record');
            return 1;
        }

        $this->info("Using Italy country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Italy provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'ایتالیا')->first();

        if (!$country) {
            $this->info('Creating Italy country record...');
            $country = Country::create([
                'title' => 'ایتالیا',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Italy)...');

        $provinceName = 'ایتالیا';

        $exists = Province::where('title', $provinceName)
            ->where('country_id', $country->id)
            ->exists();

        if (!$exists || $force) {
            if ($force && $exists) {
                Province::where('title', $provinceName)
                    ->where('country_id', $country->id)
                    ->update(['status' => 1]);
            } else {
                Province::create([
                    'title' => $provinceName,
                    'country_id' => $country->id,
                    'status' => 1
                ]);
            }
        }

        $this->info('Province populated successfully!');
    }

    private function populateCities(Country $country, bool $force): void
    {
        $this->info('Populating cities...');

        $province = Province::where('title', 'ایتالیا')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Italy province not found. Please run --only-provinces first.');
            return;
        }

        $cities = $this->getCitiesData();
        $bar = $this->output->createProgressBar(count($cities));
        $bar->start();

        foreach ($cities as $cityName) {
            $exists = City::where('title', $cityName)
                ->where('province_id', $province->id)
                ->exists();

            if (!$exists || $force) {
                if ($force && $exists) {
                    City::where('title', $cityName)
                        ->where('province_id', $province->id)
                        ->update(['status' => 1]);
                } else {
                    City::create([
                        'title' => $cityName,
                        'province_id' => $province->id,
                        'status' => 1
                    ]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Cities populated successfully!');
    }

    private function getCitiesData(): array
    {
        return [
            'رم', // Rome
            'میلان', // Milan
            'ناپل', // Naples
            'تورین', // Turin
            'پالرمو', // Palermo
            'جنوا', // Genoa
            'بولونیا', // Bologna
            'فلورانس', // Florence
            'باری', // Bari
            'کاتانیا', // Catania
            'ونیز', // Venice
            'ورونا', // Verona
            'مسینا', // Messina
            'پادووا', // Padua
            'تریسته', // Trieste
            'تارانتو', // Taranto
            'برشا', // Brescia
            'پراتو', // Prato
            'رجو کالابریا', // Reggio Calabria
            'مودنا', // Modena
            'رگیو امیلیا', // Reggio Emilia
            'پروجا', // Perugia
            'راونا', // Ravenna
            'لیوورنو', // Livorno
            'کالیاری', // Cagliari
            'فوجا', // Foggia
            'ریمینی', // Rimini
            'سالرنو', // Salerno
            'فررارا', // Ferrara
            'ساسری', // Sassari
            'لاتینا', // Latina
            'جیولیانووا', // Giulianova
            'مونزا', // Monza
            'سیراکوز', // Syracuse
            'پیزا', // Pisa
            'برگامو', // Bergamo
            'ویچنزا', // Vicenza
            'تیرنی', // Terni
            'بولزانو', // Bolzano
            'نووارا', // Novara
            'آنکونا', // Ancona
            'آندریا', // Andria
            'اودینه', // Udine
            'آری‌تسو', // Arezzo
            'چزنا', // Cesena
            'لچه', // Lecce
            'پسکارا', // Pescara
            'لا اسپتسیا', // La Spezia
            'برلتو', // Brindisi
            'پیاچنزا', // Piacenza
            'کاسرتا', // Caserta
            'کارارا', // Carrara
            'فورلی', // Forlì
            'کرمونا', // Cremona
            'امپولی', // Empoli
            'کوزنزا', // Cosenza
            'ایمولا', // Imola
        ];
    }
}
