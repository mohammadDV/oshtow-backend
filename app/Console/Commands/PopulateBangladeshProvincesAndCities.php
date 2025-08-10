<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateBangladeshProvincesAndCities extends Command
{
    protected $signature = 'populate:bangladesh-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Bangladesh provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Bangladesh provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Bangladesh country record');
            return 1;
        }

        $this->info("Using Bangladesh country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Bangladesh provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'بنگلادش')->first();

        if (!$country) {
            $this->info('Creating Bangladesh country record...');
            $country = Country::create([
                'title' => 'بنگلادش',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Bangladesh)...');

        $provinceName = 'بنگلادش';

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

        $province = Province::where('title', 'بنگلادش')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Bangladesh province not found. Please run --only-provinces first.');
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
            'داکا', // Dhaka
            'چیتاگونگ', // Chittagong
            'کهلنا', // Khulna
            'راجشاهی', // Rajshahi
            'سیلیت', // Sylhet
            'بریسال', // Barisal
            'رنگپور', // Rangpur
            'کومیلا', // Cumilla
            'گازی‌پور', // Gazipur
            'ناراینگج', // Narayanganj
            'تونگی', // Tongi
            'مایمینسینگ', // Mymensingh
            'بوگره', // Bogra
            'جیسور', // Jessore
            'نواب‌گنج', // Nawabganj
            'دیناج‌پور', // Dinajpur
            'جمال‌پور', // Jamalpur
            'فریدپور', // Faridpur
            'پابنا', // Pabna
            'کوشتیا', // Kushtia
            'تکسال', // Tangail
            'کیشوره‌گنج', // Kishoreganj
            'چوداگره', // Chuadanga
            'شریات‌پور', // Shariatpur
            'مادریپور', // Madaripur
            'گوپال‌گنج', // Gopalganj
            'نارائل', // Narail
            'بگره', // Bagherhat
            'یاکووب', // Yakub
            'کوکس بازار', // Cox's Bazar
            'راموگره', // Ramgarh
            'براهمان بریا', // Brahmanbaria
            'فنی', // Feni
            'لاکشمی‌پور', // Lakshmipur
            'نوآکھلی', // Noakhali
            'چندپور', // Chandpur
            'مونلا', // Munla
            'پتواکھلی', // Patuakhali
            'بھولا', // Bhola
            'جھالکاٹی', // Jhalokati
            'برگونا', // Barguna
            'پنچکار', // Panchagarh
            'لال‌مونیرهات', // Lalmonirhat
            'کوریگرام', // Kurigram
            'گائیباندا', // Gaibandha
            'نیل‌فاماری', // Nilphamari
            'ٹکورگاون', // Thakurgaon
            'نوگایون', // Nawabganj
            'جو‌پورهات', // Joypurhat
            'شیرپور', // Sherpur
            'نیتراکونا', // Netrakona
        ];
    }
}