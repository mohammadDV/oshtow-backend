<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateArmeniaProvincesAndCities extends Command
{
    protected $signature = 'populate:armenia-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Armenia provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Armenia provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Armenia country record');
            return 1;
        }

        $this->info("Using Armenia country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Armenia provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'ارمنستان')->first();

        if (!$country) {
            $this->info('Creating Armenia country record...');
            $country = Country::create([
                'title' => 'ارمنستان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Armenia)...');

        $provinceName = 'ارمنستان';

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

        $province = Province::where('title', 'ارمنستان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Armenia province not found. Please run --only-provinces first.');
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
            'ایروان', // Yerevan
            'گیومری', // Gyumri
            'وانادزور', // Vanadzor
            'اچمیادزین', // Etchmiadzin
            'هرازدان', // Hrazdan
            'آبوویان', // Abovyan
            'کاپان', // Kapan
            'آرمویر', // Armavir
            'ارتاشات', // Artashat
            'آلاورتی', // Alaverdi
            'سیسیان', // Sisian
            'مرجاوان', // Marghahovit
            'چاراتار', // Chartar
            'یغواردو', // Yeghvard
            'تاشیر', // Tashir
            'گوریس', // Goris
            'آشتاراک', // Ashtarak
            'سوان', // Sevan
            'بیارد', // Byuard
            'واردنیک', // Vardenis
            'مرتونی', // Martuni
            'ثالین', // Tsalendjikha
            'گافان', // Gafan
            'آکنا', // Akhna
            'دیلیژان', // Dilian
            'نویوروکو', // Noyemberyan
            'سپیداک', // Spitak
            'کار', // Kar
            'بیرد', // Berd
            'شوشی', // Shushi
            'مغرض', // Maghriz
            'ورتان', // Vartenis
            'کامو', // Kamo
            'تونل', // Tunnel
            'اراکس', // Arax
            'ارمونا', // Armona
            'مالاتیا', // Malatya
            'مراز', // Maraz
            'نورک', // Nork
            'متروپول', // Metropol
            'کنکر', // Kenkerr
            'هندستان', // Hindustan
            'اوترا', // Otra
            'ببکر', // Babakr
            'ریسا', // Risa
            'ماشتوتس', // Mashtots
            'نالیندا', // Nalintha
            'چنگاود', // Chunghawad
            'هایرین', // Hayerin
            'ورتان', // Vertanik
        ];
    }
}