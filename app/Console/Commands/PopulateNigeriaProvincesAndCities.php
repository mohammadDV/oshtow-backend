<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateNigeriaProvincesAndCities extends Command
{
    protected $signature = 'populate:nigeria-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Nigeria provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Nigeria provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Nigeria country record');
            return 1;
        }

        $this->info("Using Nigeria country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Nigeria provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'نیجریه')->first();

        if (!$country) {
            $this->info('Creating Nigeria country record...');
            $country = Country::create([
                'title' => 'نیجریه',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Nigeria)...');

        $provinceName = 'نیجریه';

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

        $province = Province::where('title', 'نیجریه')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Nigeria province not found. Please run --only-provinces first.');
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
            'لاگوس', // Lagos
            'کانو', // Kano
            'ابوجا', // Abuja
            'ایبادان', // Ibadan
            'پورت هارکورت', // Port Harcourt
            'بنین سیتی', // Benin City
            'مایدوگوری', // Maiduguri
            'زاریا', // Zaria
            'آبا', // Aba
            'یوس', // Jos
            'ایلورین', // Ilorin
            'اوگبوموشو', // Ogbomoso
            'کادونا', // Kaduna
            'سوکوتو', // Sokoto
            'اوویری', // Owerri
            'بایدا', // Bauchi
            'اکیره', // Akure
            'اونیتشا', // Onitsha
            'وارری', // Warri
            'کالابار', // Calabar
            'اینوگو', // Enugu
            'آبئکوتا', // Abeokuta
            'اولوین', // Ilowen
            'مینا', // Minna
            'ادو اکیتی', // Ado-Ekiti
            'گومبه', // Gombe
            'اوگیو', // Uyo
            'اگبور', // Agbor
            'آساباه', // Asaba
            'دامتورو', // Damaturu
            'یولا', // Yola
            'کاتسینا', // Katsina
            'گوساو', // Gusau
            'بیرنین کبی', // Birnin Kebbi
            'گاروا', // Garowe
            'دوتسه', // Dutse
            'جلینگا', // Jalingo
            'لافیا', // Lafia
            'اکور', // Okuke
            'اویو', // Oyo
            'اگبور', // Agbor
            'سپله', // Sapele
            'اونادو', // Ondo
            'آراه', // Arah
            'ایوو', // Owo
            'اکولوی', // Ikole
            'ایجرو', // Ijero
            'اولاریمی', // Olaluye
            'ایسیین', // Iseyin
            'لادو', // Lado
            'شاگامو', // Sagamu
            'ایجیبو اود', // Ijebu Ode
            'ایبه', // Iba
            'بدگری', // Badagry
            'اپاپا', // Apapa
            'مومبا', // Mushin
            'ایکیجا', // Ikeja
            'ایکیجا', // Ikeja
            'یابا', // Yaba
            'ویکتوریا آیلند', // Victoria Island
            'گروین', // Garki
            'وویسه', // Wuse
            'آسوکورو', // Asokoro
            'گویاریا', // Gwarinpa
            'کوگی', // Kogi
            'سوله تانکه', // Suleja
        ];
    }
}
