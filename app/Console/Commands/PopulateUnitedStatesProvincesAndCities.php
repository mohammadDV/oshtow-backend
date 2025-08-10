<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateUnitedStatesProvincesAndCities extends Command
{
    protected $signature = 'populate:united-states-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate United States provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting United States provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create United States country record');
            return 1;
        }

        $this->info("Using United States country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('United States provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'آمریکا')->first();

        if (!$country) {
            $this->info('Creating United States country record...');
            $country = Country::create([
                'title' => 'آمریکا',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (United States)...');

        $provinceName = 'آمریکا';

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

        $province = Province::where('title', 'آمریکا')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('United States province not found. Please run --only-provinces first.');
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
            'نیویورک', // New York
            'لس آنجلس', // Los Angeles
            'شیکاگو', // Chicago
            'هیوستون', // Houston
            'فینیکس', // Phoenix
            'فیلادلفیا', // Philadelphia
            'سن آنتونیو', // San Antonio
            'سن دیگو', // San Diego
            'دالاس', // Dallas
            'سن خوزه', // San Jose
            'آستین', // Austin
            'جکسون ویل', // Jacksonville
            'فورت ورث', // Fort Worth
            'کلمبوس', // Columbus
            'ایندیاناپولیس', // Indianapolis
            'شارلوت', // Charlotte
            'سان فرانسیسکو', // San Francisco
            'سیتل', // Seattle
            'دنور', // Denver
            'بوستون', // Boston
            'ال پاسو', // El Paso
            'دیترویت', // Detroit
            'ناشویل', // Nashville
            'ممفیس', // Memphis
            'پورتلند', // Portland
            'اوکلاهما سیتی', // Oklahoma City
            'لاس وگاس', // Las Vegas
            'لویزویل', // Louisville
            'بالتیمور', // Baltimore
            'میلواکی', // Milwaukee
            'آلبوکرکه', // Albuquerque
            'توکسون', // Tucson
            'فرسنو', // Fresno
            'ساکرامنتو', // Sacramento
            'آتلانتا', // Atlanta
            'کانزاس سیتی', // Kansas City
            'کولورادو اسپرینگز', // Colorado Springs
            'میامی', // Miami
            'رالی', // Raleigh
            'اوماها', // Omaha
            'لانگ بیچ', // Long Beach
            'ویرجینیا بیچ', // Virginia Beach
            'اوکلند', // Oakland
            'مینیاپولیس', // Minneapolis
            'تولسا', // Tulsa
            'ارلینگتون', // Arlington
            'تمپا', // Tampa
            'نیو اورلینز', // New Orleans
            'ویچیتا', // Wichita
            'کلیولند', // Cleveland
            'بیکرزفیلد', // Bakersfield
            'آنهایم', // Anaheim
            'هونولولو', // Honolulu
            'سنت پیترزبورگ', // St. Petersburg
            'استکتون', // Stockton
            'سینسیناتی', // Cincinnati
            'انکریج', // Anchorage
            'کورپوس کریستی', // Corpus Christi
            'لکسینگتون', // Lexington
            'هندرسون', // Henderson
            'گرینزبورو', // Greensboro
            'پلانو', // Plano
            'نیوارک', // Newark
            'لینکلن', // Lincoln
            'بوفالو', // Buffalo
            'ژرسی سیتی', // Jersey City
            'چولا ویستا', // Chula Vista
            'فورت وین', // Fort Wayne
            'اورلاندو', // Orlando
            'سنت پل', // St. Paul
            'چندلر', // Chandler
            'نورفولک', // Norfolk
            'داربی', // Durham
        ];
    }
}
