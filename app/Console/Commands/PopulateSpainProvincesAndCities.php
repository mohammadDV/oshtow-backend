<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateSpainProvincesAndCities extends Command
{
    protected $signature = 'populate:spain-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Spain provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Spain provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Spain country record');
            return 1;
        }

        $this->info("Using Spain country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Spain provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'اسپانیا')->first();

        if (!$country) {
            $this->info('Creating Spain country record...');
            $country = Country::create([
                'title' => 'اسپانیا',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Spain)...');

        $provinceName = 'اسپانیا';

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

        $province = Province::where('title', 'اسپانیا')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Spain province not found. Please run --only-provinces first.');
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
            'مادرید', // Madrid
            'بارسلونا', // Barcelona
            'والنسیا', // Valencia
            'سویا', // Seville
            'ساراگوسا', // Zaragoza
            'مالاگا', // Málaga
            'مورسیا', // Murcia
            'پالماس', // Las Palmas
            'بیلبائو', // Bilbao
            'آلیکانته', // Alicante
            'کوردوبا', // Córdoba
            'والادولید', // Valladolid
            'ویگو', // Vigo
            'خیخون', // Gijón
            'اسپیتالت', // L'Hospitalet
            'کورونیا', // A Coruña
            'گرانادا', // Granada
            'ویتوریا', // Vitoria-Gasteiz
            'الچه', // Elche
            'سانتا کروز', // Santa Cruz
            'اویدو', // Oviedo
            'بادالونا', // Badalona
            'کارتاخنا', // Cartagena
            'تراسا', // Terrassa
            'خرس', // Jerez
            'سابادل', // Sabadell
            'موستولز', // Móstoles
            'پامپلونا', // Pamplona
            'آلکالا', // Alcalá de Henares
            'فوئنلابرادا', // Fuenlabrada
            'لئون', // León
            'المریا', // Almería
            'بورگوس', // Burgos
            'آلباسته', // Albacete
            'گتافه', // Getafe
            'سالامانکا', // Salamanca
            'کادیس', // Cádiz
            'لگانس', // Leganés
            'سانتاندر', // Santander
            'کاستیون', // Castellón
            'آلکورکن', // Alcorcón
            'لورکا', // Lorca
            'بدایوس', // Badajoz
            'تاراگونا', // Tarragona
            'خائن', // Jaén
            'رئوس', // Reus
            'ماتارو', // Mataró
            'کاسکس', // Cáceres
            'سانتیاگو', // Santiago
        ];
    }
}