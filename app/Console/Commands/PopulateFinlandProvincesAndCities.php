<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateFinlandProvincesAndCities extends Command
{
    protected $signature = 'populate:finland-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Finland provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Finland provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Finland country record');
            return 1;
        }

        $this->info("Using Finland country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Finland provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'فنلاند')->first();

        if (!$country) {
            $this->info('Creating Finland country record...');
            $country = Country::create([
                'title' => 'فنلاند',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Finland)...');

        $provinceName = 'فنلاند';

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

        $province = Province::where('title', 'فنلاند')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Finland province not found. Please run --only-provinces first.');
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
            'هلسینکی', // Helsinki
            'اسپو', // Espoo
            'تامپره', // Tampere
            'وانتا', // Vantaa
            'اولو', // Oulu
            'تورکو', // Turku
            'یوواسکولا', // Jyväskylä
            'لاهتی', // Lahti
            'کووپیو', // Kuopio
            'پوری', // Pori
            'یوینسو', // Joensuu
            'لاپین رانتا', // Lappeenranta
            'هامینلینا', // Hämeenlinna
            'وآسا', // Vaasa
            'هیونکا', // Hyvinkää
            'مایکلی', // Mikkeli
            'کوتکا', // Kotka
            'سینیمیکی', // Seinäjoki
            'روونیمی', // Rovaniemi
            'سالو', // Salo
            'پوروو', // Porvoo
            'کوولا', // Kouvola
            'یاروانپا', // Järvenpää
            'رایسیو', // Raasio
            'تویوالا', // Tuusula
            'کئوراوا', // Kerava
            'سووی', // Savonlinna
            'کاریایننن', // Kärjäinen
            'لیمینکا', // Liminka
            'ایئاتلی', // Imatra
            'ریہیمیکی', // Riihimäki
            'نورکیا', // Nurmijärvi
            'کائونیا', // Kajaani
            'یوگوین', // Jyväskylä
            'کاریکنا', // Kaarina
            'رائومانا', // Raisio
            'کاکویلا', // Kauhava
            'نووگارد', // Naantali
            'پیکسامیکی', // Pieksämäki
            'ولکیاکوسکی', // Valkeakoski
            'آکیا', // Akaa
            'فورسسا', // Forssa
            'کانکانپیا', // Kankaanpää
            'اوریمیتیلا', // Orimattila
            'لوهجا', // Lohja
            'اولویلا', // Ulvila
        ];
    }
}
