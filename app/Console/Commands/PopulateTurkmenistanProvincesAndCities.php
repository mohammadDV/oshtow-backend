<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateTurkmenistanProvincesAndCities extends Command
{
    protected $signature = 'populate:turkmenistan-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Turkmenistan provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Turkmenistan provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Turkmenistan country record');
            return 1;
        }

        $this->info("Using Turkmenistan country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Turkmenistan provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'ترکمنستان')->first();

        if (!$country) {
            $this->info('Creating Turkmenistan country record...');
            $country = Country::create([
                'title' => 'ترکمنستان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Turkmenistan)...');

        $provinceName = 'ترکمنستان';

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

        $province = Province::where('title', 'ترکمنستان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Turkmenistan province not found. Please run --only-provinces first.');
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
            'عشق آباد', // Ashgabat
            'ترکمن آباد', // Turkmenbashi
            'دشوغوز', // Dashoguz
            'تورکمن', // Turkmen
            'بالکان آباد', // Balkanabat
            'سردار', // Serdar
            'گوک تپه', // Goktepe
            'مار', // Mary
            'بایرام علی', // Bayramaly
            'تیزن', // Tejen
            'قارشی', // Qarshi
            'کونیه اورگنچ', // Kone-Urgench
            'تاخیاتش', // Takhiatash
            'گومدغ', // Guymdag
            'آچک', // Achak
            'اتامغرات', // Etamgrat
            'تورکمن قلعه', // Turkmenkala
            'چاردژو', // Chardzhou
            'کلیف', // Kelif
            'کرکی', // Kerki
            'آتامردت', // Atamurat
            'حاجی پیل', // Hajypyl
            'دامله', // Damla
            'یولتن', // Yoloten
            'ساکار', // Sakar
            'آنیو', // Annau
            'ایپک یولی', // Ipek Yoli
            'الت آرام', // Altyn Asyr
            'کپل دوول', // Kopetdag
            'گوگورلی', // Gugerel
            'آچگابات', // Achgabat
            'نیبت داغ', // Nebit Dag
            'کیزیل آرغات', // Kizyl Arvat
            'گازانجیک', // Gazanjyk
            'ماختوم قولی', // Magtymguly
            'تورکمن باشی', // Turkmenbashi City
            'هازار', // Hazar
            'قراداغلی', // Garadaghly
            'اکیاک', // Akyab
            'ایرغیز', // Erbent
            'گوزک', // Gozak
            'قطار', // Gatar
            'کوش رباط', // Kosh Rabat
            'گرگان', // Gorgan
            'شیل', // Shil
            'اتکان', // Etakan
            'سویول', // Soyol
        ];
    }
}