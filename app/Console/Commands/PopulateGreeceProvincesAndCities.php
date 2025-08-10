<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateGreeceProvincesAndCities extends Command
{
    protected $signature = 'populate:greece-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Greece provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Greece provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Greece country record');
            return 1;
        }

        $this->info("Using Greece country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Greece provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'یونان')->first();

        if (!$country) {
            $this->info('Creating Greece country record...');
            $country = Country::create([
                'title' => 'یونان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Greece)...');

        $provinceName = 'یونان';

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

        $province = Province::where('title', 'یونان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Greece province not found. Please run --only-provinces first.');
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
            'آتن', // Athens
            'تسالونیکی', // Thessaloniki
            'پاتراس', // Patras
            'هراکلیون', // Heraklion
            'لاریسا', // Larissa
            'والوس', // Volos
            'یوآنینا', // Ioannina
            'تریکالا', // Trikala
            'خالکیس', // Chalkis
            'سرس', // Serres
            'گیانیتسا', // Giannitsa
            'کاترینی', // Katerini
            'رودس', // Rhodes
            'آگرینیو', // Agrinio
            'کاردیتسا', // Karditsa
            'کاولا', // Kavala
            'خانیا', // Chania
            'لامیا', // Lamia
            'کوموتینی', // Komotini
            'میتیلنی', // Mytilene
            'کیلکیس', // Kilkis
            'کوزانی', // Kozani
            'وریا', // Veria
            'آلکساندروپولیس', // Alexandroupolis
            'کوس', // Kos
            'رتیمنو', // Rethymno
            'تریپولی', // Tripoli
            'پیرگوس', // Pyrgos
            'درامه', // Drama
            'کورینتوس', // Corinth
            'کالامه', // Kalamata
            'آرتا', // Arta
            'آیگیو', // Aigio
            'سپارتی', // Sparta
            'پروزا', // Preveza
            'مگاره', // Megara
            'کرکیره', // Kerkyra
            'آرگوس', // Argos
            'کاستوریا', // Kastoria
            'فلورینا', // Florina
            'زاکینتوس', // Zakynthos
            'گروینا', // Grevena
            'نافپلیو', // Nafplio
            'لیوادیا', // Livadeia
            'چیوس', // Chios
            'کالیتیا', // Kalymnos
            'آموس', // Samos
            'ریتیمنو', // Rethymno
            'چالکیدیکی', // Chalkidiki
        ];
    }
}
