<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateNorwayProvincesAndCities extends Command
{
    protected $signature = 'populate:norway-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Norway provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Norway provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Norway country record');
            return 1;
        }

        $this->info("Using Norway country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Norway provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'نروژ')->first();

        if (!$country) {
            $this->info('Creating Norway country record...');
            $country = Country::create([
                'title' => 'نروژ',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Norway)...');

        $provinceName = 'نروژ';

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

        $province = Province::where('title', 'نروژ')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Norway province not found. Please run --only-provinces first.');
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
            'اسلو', // Oslo
            'برگن', // Bergen
            'ترندهیم', // Trondheim
            'ستاوانگر', // Stavanger
            'بائرم', // Bærum
            'کریستیانساند', // Kristiansand
            'فردریکستاد', // Fredrikstad
            'سانندفیورد', // Sandefjord
            'ترومسو', // Tromsø
            'سارپسبورگ', // Sarpsborg
            'اسکین', // Skien
            'اودرن', // Ålesund
            'تونسبرگ', // Tønsberg
            'موس', // Moss
            'بودو', // Bodø
            'آرندال', // Arendal
            'هامار', // Hamar
            'یلیهامر', // Lillehammer
            'هالدن', // Halden
            'کونگسبرگ', // Kongsberg
            'هرمار', // Harstad
            'پورسگرون', // Porsgrunn
            'اولسوند', // Ålesund
            'مولده', // Molde
            'هوتن', // Horten
            'گجویک', // Gjøvik
            'راندرز', // Ringerike
            'ریسر', // Risør
            'کرگرو', // Kragerø
            'نروک', // Narvik
            'نوتودن', // Notodden
            'فلکفیورد', // Flekkefjord
            'اجرسند', // Egersund
            'اوستایمیل', // Alta
            'مو ای رانا', // Mo i Rana
            'کیرکنس', // Kirkenes
            'وادسو', // Vadsø
            'هامرفست', // Hammerfest
            'هونینگسواگ', // Honningsvåg
            'لیلساند', // Lillesand
            'مانדال', // Mandal
            'فرسن', // Førde
            'سوگندال', // Sogndal
            'اولسان', // Ulsan
            'برومویا', // Brumunddal
            'کرنیا', // Krana
            'اوردالسلوکا', // Ørdalslokka
        ];
    }
}