<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateBahrainProvincesAndCities extends Command
{
    protected $signature = 'populate:bahrain-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Bahrain provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Bahrain provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Bahrain country record');
            return 1;
        }

        $this->info("Using Bahrain country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Bahrain provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'بحرین')->first();

        if (!$country) {
            $this->info('Creating Bahrain country record...');
            $country = Country::create([
                'title' => 'بحرین',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Bahrain)...');

        $provinceName = 'بحرین';

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

        $province = Province::where('title', 'بحرین')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Bahrain province not found. Please run --only-provinces first.');
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
            'منامه', // Manama
            'مدینه عیسی', // Madīnat 'Īsā
            'رفاع', // Riffa
            'مدینه حمد', // Madīnat Ḥamad
            'المحرق', // Muharraq
            'علی', // A'ali
            'سترا', // Sitra
            'بدیا', // Budaiya
            'جد حفص', // Jidhafs
            'سلماباد', // Salmanabad
            'دراز', // Diraz
            'توبلی', // Tubli
            'البحیر', // Al Bahir
            'کرانه', // Karana
            'الحلانیا', // Hillunya
            'عراد', // Arad
            'دمستان', // Dumastan
            'سهله', // Sahlah
            'سند', // Sanad
            'عسکر', // Askar
            'المارک', // Al-Malikiyah
            'الدیر', // Aldair
            'کرزکان', // Karzakan
            'اوال', // Awal
            'النعیم', // Naim
            'النویدرات', // Nuwaidrat
            'دار کلیب', // Dar Kulaib
            'قریه', // Qalali
            'الجنبیه', // Janabiya
            'عالی', // Aali
            'حوار', // Hawar
            'المالکیه', // Malikiya
            'الجملا', // Ja'imla
            'البسیطه', // Basitah
            'مرکز', // Markaz
            'ام الحصم', // Umm al-Hassam
            'قدم', // Qodam
            'صدد', // Saddad
            'الحد', // Al-Hadd
            'عوالی', // Awali
            'السنابس', // Sanabis
            'الزلاق', // Zallaq
            'عدلیا', // Adliya
            'الجفیر', // Jufair
            'الغریفه', // Ghurayfia
            'الحجیات', // Hajiyat
        ];
    }
}
