<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateSwedenProvincesAndCities extends Command
{
    protected $signature = 'populate:sweden-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Sweden provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Sweden provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Sweden country record');
            return 1;
        }

        $this->info("Using Sweden country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Sweden provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'سوئد')->first();

        if (!$country) {
            $this->info('Creating Sweden country record...');
            $country = Country::create([
                'title' => 'سوئد',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Sweden)...');

        $provinceName = 'سوئد';

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

        $province = Province::where('title', 'سوئد')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Sweden province not found. Please run --only-provinces first.');
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
            'استکهلم', // Stockholm
            'گوتنبرگ', // Gothenburg
            'مالمو', // Malmö
            'اوپسالا', // Uppsala
            'اورپرو', // Örebro
            'لینشوپینگ', // Linköping
            'هلسینگبورگ', // Helsingborg
            'ینشوپینگ', // Jönköping
            'نورشوپینگ', // Norrköping
            'لوند', // Lund
            'اوئوما', // Umeå
            'گولی', // Gävle
            'بوراس', // Borås
            'سودرتلیه', // Södertälje
            'اسکیلتونا', // Eskilstuna
            'هالمستاد', // Halmstad
            'وکشو', // Växjö
            'کارلستاد', // Karlstad
            'سوندسوال', // Sundsvall
            'لولئو', // Luleå
            'ترولهتان', // Trollhättan
            'اوسترسوند', // Östersund
            'بودن', // Borlänge
            'تومبا', // Tumba
            'فالون', // Falun
            'کیرونا', // Kiruna
            'کالمار', // Kalmar
            'کریستیانستاد', // Kristianstad
            'کارلسکونا', // Karlskrona
            'سکی', // Skövde
            'اودوالا', // Uddevalla
            'مورکوم', // Mariestad
            'سندویکن', // Sandviken
            'اولمو', // Malmö
            'سولنا', // Solna
            'سودرام', // Söderhamn
            'یستاد', // Ystad
            'هودینگ', // Hudiksvall
            'بورلنگه', // Borlänge
            'انگلهولم', // Ängelholm
            'لیدینگو', // Lidingö
            'انشوپینگ', // Enköping
            'بیدن', // Visby
            'مورا', // Mora
            'ترنبرگ', // Trelleborg
            'ساندویکن', // Sandviken
            'هاسلهولم', // Hässleholm
            'آویستا', // Avesta
        ];
    }
}