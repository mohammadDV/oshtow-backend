<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateUzbekistanProvincesAndCities extends Command
{
    protected $signature = 'populate:uzbekistan-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Uzbekistan provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Uzbekistan provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Uzbekistan country record');
            return 1;
        }

        $this->info("Using Uzbekistan country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Uzbekistan provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'ازبکستان')->first();

        if (!$country) {
            $this->info('Creating Uzbekistan country record...');
            $country = Country::create([
                'title' => 'ازبکستان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Uzbekistan)...');

        $provinceName = 'ازبکستان';

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

        $province = Province::where('title', 'ازبکستان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Uzbekistan province not found. Please run --only-provinces first.');
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
            'تاشکند', // Tashkent
            'سمرقند', // Samarkand
            'نمنگان', // Namangan
            'اندیجان', // Andijan
            'نوکوس', // Nukus
            'بخارا', // Bukhara
            'فرغانه', // Fergana
            'کرشی', // Karshi
            'کوکند', // Kokand
            'مارگیلان', // Margilan
            'ترمز', // Termez
            'چیرچیق', // Chirchik
            'اورگنچ', // Urgench
            'جیزک', // Jizzakh
            'نوائی', // Navoiy
            'آنگرن', // Angren
            'ریشتان', // Rishtan
            'کتیاکورگان', // Kitab
            'کاگان', // Kagan
            'گولیستان', // Gulistan
            'یانگیر', // Yangier
            'بکاباد', // Bekabad
            'مبارک', // Mubarek
            'کنیمخ', // Kanimekh
            'شاهرخان', // Shahrikhan
            'بوستانلیق', // Bostanliq
            'گالا اسار', // Gala-Asiar
            'الماته', // Almalyk
            'تلماسان', // Tashkent
            'یانگی شهر', // Yangishahar
            'نارین', // Narin
            'بونیودکور', // Boniodkor
            'یانگی حیات', // Yangihayot
            'شافرکان', // Shahrikan
            'پاپ', // Pop
            'کوشتپه', // Kushtapah
            'بوختار', // Boysun
            'سردوبا', // Sardoba
            'مینگ چ کور', // Mingchukur
            'اولتی اریق', // Oltiariq
            'باگدد', // Bagdad
            'سو خ', // Sukh
            'خیوه', // Khiva
            'کنکا', // Kanka
            'پختاکور', // Pakhtakor
            'یوکری چیرچیق', // Yukori Chirchiq
            'زنگیاته', // Zangiata
            'طولانگیت', // Tulanget
            'سالار', // Salar
        ];
    }
}