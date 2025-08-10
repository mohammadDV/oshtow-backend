<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateKazakhstanProvincesAndCities extends Command
{
    protected $signature = 'populate:kazakhstan-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Kazakhstan provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Kazakhstan provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Kazakhstan country record');
            return 1;
        }

        $this->info("Using Kazakhstan country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Kazakhstan provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'قزاقستان')->first();

        if (!$country) {
            $this->info('Creating Kazakhstan country record...');
            $country = Country::create([
                'title' => 'قزاقستان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Kazakhstan)...');

        $provinceName = 'قزاقستان';

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

        $province = Province::where('title', 'قزاقستان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Kazakhstan province not found. Please run --only-provinces first.');
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
            'آستانه', // Nur-Sultan (Astana)
            'آلماتی', // Almaty
            'شیمکنت', // Shymkent
            'آکتوبه', // Aktobe
            'کاراگاندی', // Karaganda
            'تاراز', // Taraz
            'پاولودار', // Pavlodar
            'اوست کامنوگورسک', // Ust-Kamenogorsk
            'سمی', // Semey
            'اورالسک', // Oral
            'آتیراو', // Atyrau
            'کوستانای', // Kostanay
            'کیزیل اوردا', // Kyzylorda
            'آکتاو', // Aktau
            'پتروپاولوسک', // Petropavl
            'بالخاش', // Balkhash
            'ریدر', // Ridder
            'تورکستان', // Turkestan
            'طلدیکورگان', // Taldykorgan
            'کاندیآگاش', // Kandyagash
            'لیساکووسک', // Lisakovsk
            'آرقالیک', // Arkalyk
            'رودنی', // Rudny
            'ژزقزغان', // Zhezkazgan
            'ژانا اوزن', // Zhanaozen
            'سرقند', // Sarkand
            'ایکیباستوز', // Ekibastuz
            'کنت', // Kent
            'کاپشاغای', // Kapshagay
            'آبای', // Abay
            'ریددر', // Ridder
            'آیاکوز', // Ayakoz
            'شچوچینسک', // Shchuchinsk
            'استافنوه', // Stepnoye
            'شالکار', // Shalkar
            'شو', // Shu
            'آریس', // Arys
            'قراتاو', // Karatau
            'جتیسو', // Zhetisay
            'کازالینسک', // Kazalinsk
            'قارا بالت', // Qarabalt
            'مایکوپچاگای', // Maykopchagay
            'آکسو', // Aksu
            'قزل آغاچ', // Qyzyl Agash
            'قراساز', // Qarasaz
            'کورداي', // Kordai
            'سارکان', // Sarkan
            'تکلی', // Tekeli
            'آلگا', // Alga
            'بایکونور', // Baikonur
            'آغادیر', // Aghadir
        ];
    }
}
