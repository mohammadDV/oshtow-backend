<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulatePhilippinesProvincesAndCities extends Command
{
    protected $signature = 'populate:philippines-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Philippines provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Philippines provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Philippines country record');
            return 1;
        }

        $this->info("Using Philippines country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Philippines provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'فیلیپین')->first();

        if (!$country) {
            $this->info('Creating Philippines country record...');
            $country = Country::create([
                'title' => 'فیلیپین',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Philippines)...');

        $provinceName = 'فیلیپین';

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

        $province = Province::where('title', 'فیلیپین')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Philippines province not found. Please run --only-provinces first.');
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
            'مانیل', // Manila
            'کوزون سیتی', // Quezon City
            'کالوکان', // Caloocan
            'داوائو', // Davao
            'سبو', // Cebu City
            'زامبوانگا', // Zamboanga
            'تاگویگ', // Taguig
            'آنتی‌پولو', // Antipolo
            'پاسیگ', // Pasig
            'کاگایان د اورو', // Cagayan de Oro
            'پارانیاکوی', // Parañaque
            'لاس پیناس', // Las Piñas
            'مکاتی', // Makati
            'باکولود', // Bacolod
            'مونتین‌لوپا', // Muntinlupa
            'یلالوگ', // Iloilo City
            'مالابون', // Malabon
            'باگویو', // Baguio
            'پاسای', // Pasay
            'ولنزولا', // Valenzuela
            'جنرال سانتوس', // General Santos
            'نووالیچس', // Novaliches
            'بیتان', // Biñan
            'باکور', // Bacoor
            'آنجلس', // Angeles
            'داسماریناس', // Dasmariñas
            'سان خوسه دل مونته', // San Jose del Monte
            'کوتاباتو', // Cotabato
            'تاکلوبان', // Tacloban
            'بوتوان', // Butuan
            'لاپو لاپو', // Lapu-Lapu
            'مندالویونگ', // Mandaluyong
            'نایک', // Navotas
            'لیپا', // Lipa
            'لوسنا', // Lucena
            'دیگوپان', // Dagupan
            'تکلوبان', // Tacloban
            'کابانتوان', // Cabanatuan
            'ایلیگان', // Iligan
            'بتانگاس سیتی', // Batangas City
            'اولونگاپو', // Olongapo
            'کدیز', // Cadiz
            'تارلاک', // Tarlac
            'سان کارلوس', // San Carlos
            'مالایبالای', // Malaybalay
            'سورگیاو', // Surigao
            'بوهول', // Bohol
            'نگاکال', // Nnegros
            'پالمبانگ', // Palembang
            'سیکیلد', // Siquijor
            'کامیگین', // Camiguin
            'گینداولمان', // Guindulman
            'کورون', // Coron
            'ال نیدو', // El Nido
            'پالاوان', // Puerto Princesa
            'ماریندوکوی', // Marinduque
            'ریزال', // Rizal
            'لاگونا', // Laguna
            'کاویته', // Cavite
            'بولاکان', // Bulacan
            'پامپانگا', // Pampanga
            'بوانگا', // Bulacan
            'نووا اچیجا', // Nueva Ecija
            'بنگوت', // Benguet
            'ایلوکوس نورته', // Ilocos Norte
            'ایلوکوس سور', // Ilocos Sur
            'لا یونیون', // La Union
            'پانگاسینان', // Pangasinan
            'زامبالس', // Zambales
            'باتان', // Bataan
        ];
    }
}