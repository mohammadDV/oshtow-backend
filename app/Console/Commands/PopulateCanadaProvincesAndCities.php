<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateCanadaProvincesAndCities extends Command
{
    protected $signature = 'populate:canada-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Canada provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Canada provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Canada country record');
            return 1;
        }

        $this->info("Using Canada country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Canada provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'کانادا')->first();

        if (!$country) {
            $this->info('Creating Canada country record...');
            $country = Country::create([
                'title' => 'کانادا',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Canada)...');

        $provinceName = 'کانادا';

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

        $province = Province::where('title', 'کانادا')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Canada province not found. Please run --only-provinces first.');
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
            'تورنتو', // Toronto
            'مونترال', // Montreal
            'ونکوور', // Vancouver
            'اتاوا', // Ottawa
            'کلگری', // Calgary
            'ادمونتون', // Edmonton
            'کویبک سیتی', // Quebec City
            'وینیپگ', // Winnipeg
            'هالیفکس', // Halifax
            'ویکتوریا', // Victoria
            'رجینا', // Regina
            'ساسکاتون', // Saskatoon
            'سنت جانز', // St. John's
            'فردریکتون', // Fredericton
            'چارلوت تاون', // Charlottetown
            'وایت هورس', // Whitehorse
            'یلونایف', // Yellowknife
            'ایکالویت', // Iqaluit
            'همیلتون', // Hamilton
            'کیچنر', // Kitchener
            'لندن', // London
            'وینسور', // Windsor
            'سادبری', // Sudbury
            'کینگستون', // Kingston
            'ناگرا', // Niagara
            'گویلف', // Guelph
            'باری', // Barrie
            'سری', // Surrey
            'برنابی', // Burnaby
            'ریچمند', // Richmond
            'مارخم', // Markham
            'ولگان', // Vaughan
            'میسیساگا', // Mississauga
            'براملی', // Brampton
            'اشاوا', // Oshawa
            'آکسفورد', // Oxford
            'کمبرج', // Cambridge
            'اونتاریو', // Ontario
            'ردیر', // Red Deer
            'لتبریج', // Lethbridge
            'مدیسن هت', // Medicine Hat
            'ابتسفورد', // Abbotsford
            'کیلونا', // Kelowna
            'کامپس', // Kamloops
            'نانایمو', // Nanaimo
            'پرنس جورج', // Prince George
            'تاندر بای', // Thunder Bay
            'ترو ریورز', // Trois-Rivières
            'شربروک', // Sherbrooke
            'ساگنی', // Saguenay
            'لوی', // Lévis
            'تریل', // Trail
            'بلویل', // Belleville
            'پیتربورو', // Peterborough
            'گویلف', // Guelph
            'وودستک', // Woodstock
            'سارنیا', // Sarnia
            'چثهم', // Chatham
            'کورنوال', // Cornwall
            'موس جاو', // Moose Jaw
            'پرنس آلبرت', // Prince Albert
        ];
    }
}
