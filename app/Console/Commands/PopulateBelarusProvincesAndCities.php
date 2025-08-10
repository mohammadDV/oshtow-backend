<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateBelarusProvincesAndCities extends Command
{
    protected $signature = 'populate:belarus-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Belarus provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Belarus provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Belarus country record');
            return 1;
        }

        $this->info("Using Belarus country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Belarus provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'بلاروس')->first();

        if (!$country) {
            $this->info('Creating Belarus country record...');
            $country = Country::create([
                'title' => 'بلاروس',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Belarus)...');

        $provinceName = 'بلاروس';

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

        $province = Province::where('title', 'بلاروس')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Belarus province not found. Please run --only-provinces first.');
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
            'مینسک', // Minsk
            'گوملی', // Gomel
            'موگیلف', // Mogilev
            'ویتبسک', // Vitebsk
            'گرودنو', // Grodno
            'برست', // Brest
            'بابرویسک', // Babruysk
            'بارانوویچی', // Baranovichi
            'بوریسوف', // Borisov
            'پینسک', // Pinsk
            'اورشا', // Orsha
            'مازیر', // Mazyr
            'مولودچنو', // Molodechno
            'لیدا', // Lida
            'پولوتسک', // Polotsk
            'نووپولوتسک', // Novopolotsk
            'سوولیگورسک', // Soligorsk
            'سلوتسک', // Slutsk
            'کابرین', // Kobrin
            'نووگرودک', // Novogrudok
            'سوولیگرسک', // Salighorsk
            'ریچیتسا', // Rechytsa
            'کلمنوویچی', // Klimavichy
            'دزرژینسک', // Dzerzhinsk
            'اسیپوویچی', // Asipovichy
            'زلوبین', // Zhlobin
            'فانیپول', // Fanipol
            'کپیل', // Kapyl
            'کریچف', // Krichev
            'سوینیتسا', // Svetlogorsk
            'گلیوسه', // Glusk
            'رگاچوف', // Rahachow
            'بیرزا', // Bereza
            'ایوانوو', // Ivyanets
            'کوپیل', // Kupava
            'چرکن', // Cherven
            'بیلینیچی', // Belynichy
            'پریلوکی', // Pruzhany
            'دروگچین', // Drogichyn
            'میادل', // Myadel
            'کمنکا', // Kamenka
            'وولکووسک', // Volkovysk
            'دیسنا', // Disna
            'نیسویژ', // Nesvizh
            'کلتسک', // Kletsk
            'دوبرووا', // Dubrovna
            'مستیسلال', // Mstsislal
        ];
    }
}