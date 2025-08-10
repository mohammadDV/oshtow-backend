<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateTajikistanProvincesAndCities extends Command
{
    protected $signature = 'populate:tajikistan-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Tajikistan provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Tajikistan provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Tajikistan country record');
            return 1;
        }

        $this->info("Using Tajikistan country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Tajikistan provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'تاجیکستان')->first();

        if (!$country) {
            $this->info('Creating Tajikistan country record...');
            $country = Country::create([
                'title' => 'تاجیکستان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Tajikistan)...');

        $provinceName = 'تاجیکستان';

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

        $province = Province::where('title', 'تاجیکستان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Tajikistan province not found. Please run --only-provinces first.');
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
            'دوشنبه', // Dushanbe
            'خجند', // Khujand
            'بخارا', // Bukhara
            'کولاب', // Kulob
            'قورغان تپه', // Qurghonteppa
            'حصار', // Hisor
            'ایسفارا', // Istaravshan
            'ترمذ', // Termez
            'پنجکنت', // Panjakent
            'کانی‌بادام', // Kanibadam
            'چکالوفسک', // Chkalovsk
            'یوان', // Yavan
            'روداکی', // Rudaki
            'شاهرینو', // Shahrino
            'کایراک کوم', // Kayrakum
            'نوراک', // Nurek
            'طولوک', // Tursunzoda
            'رشت', // Rasht
            'خارق', // Khayraq
            'سوغد', // Sughd
            'خاتلون', // Khatlon
            'گورنو بدخشان', // Gorno-Badakhshan
            'نوهاد', // Novabad
            'کوفارنیهون', // Kofarnihon
            'حاج عبدالله', // Hajji Abdullah
            'بودی', // Budi
            'جیرگاتال', // Jirgatol
            'فرخار', // Farkhor
            'هامادونی', // Hamadoni
            'یاوان', // Yavan
            'دانگارا', // Dangara
            'خواجه', // Khoja
            'بوستان', // Bostan
            'اسپره', // Esparre
            'اوژون', // Uzun
            'پیانج', // Panj
            'اشکاشم', // Ishkashim
            'موراب', // Murghab
            'خورج', // Khorugh
            'ونچ', // Vanch
            'دروز', // Darvoz
            'تایملی', // Taymali
            'نیکل', // Kal-i Khumb
            'گولی', // Guli
            'قرغن', // Qarghon
            'آلولی', // Aluli
            'ترگاری', // Tergari
        ];
    }
}