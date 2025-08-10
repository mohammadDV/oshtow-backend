<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateSwitzerlandProvincesAndCities extends Command
{
    protected $signature = 'populate:switzerland-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Switzerland provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Switzerland provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Switzerland country record');
            return 1;
        }

        $this->info("Using Switzerland country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Switzerland provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'سوئیس')->first();

        if (!$country) {
            $this->info('Creating Switzerland country record...');
            $country = Country::create([
                'title' => 'سوئیس',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Switzerland)...');

        $provinceName = 'سوئیس';

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

        $province = Province::where('title', 'سوئیس')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Switzerland province not found. Please run --only-provinces first.');
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
            'زوریخ', // Zurich
            'ژنو', // Geneva
            'بازل', // Basel
            'برن', // Bern
            'لوزان', // Lausanne
            'وینترتور', // Winterthur
            'لوسرن', // Lucerne
            'سن گالن', // St. Gallen
            'لوگانو', // Lugano
            'بیل', // Biel/Bienne
            'تون', // Thun
            'کنیگ', // Köniz
            'لا شو د فون', // La Chaux-de-Fonds
            'فرایبورگ', // Fribourg
            'شافهاوزن', // Schaffhausen
            'خور', // Chur
            'ورنیه', // Vernier
            'نویشاتل', // Neuchâtel
            'اوستر موندیگن', // Uster
            'سیون', // Sion
            'ایمن', // Emmen
            'یوریون', // Yverdon-les-Bains
            'زوگ', // Zug
            'کرویتسلینگن', // Kriens
            'راپرسویل یونا', // Rapperswil-Jona
            'دوبندورف', // Dübendorf
            'مونتری', // Montreux
            'دیتیکون', // Dietikon
            'ریهن', // Riehen
            'ویتیکون', // Wettingen
            'ونگن', // Wangen
            'ایگل', // Aigle
            'نیون', // Nyon
            'التندورف', // Allschwil
            'رنینس', // Renens
            'کراسیز', // Carouge
            'آارائو', // Aarau
            'هورگن', // Horgen
            'بولاخ', // Bülach
            'استفا', // Steffisburg
            'پولی', // Pully
            'گلاروس', // Glarus
            'ونگیس', // Wädenswil
            'کوسناخت', // Küsnacht
            'ادلیسویل', // Adliswil
            'لنزبورگ', // Lenzburg
            'وهلن', // Wohlen
        ];
    }
}