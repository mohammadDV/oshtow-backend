<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateJordanProvincesAndCities extends Command
{
    protected $signature = 'populate:jordan-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Jordan provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Jordan provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Jordan country record');
            return 1;
        }

        $this->info("Using Jordan country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Jordan provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'اردن')->first();

        if (!$country) {
            $this->info('Creating Jordan country record...');
            $country = Country::create([
                'title' => 'اردن',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Jordan)...');

        $provinceName = 'اردن';

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

        $province = Province::where('title', 'اردن')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Jordan province not found. Please run --only-provinces first.');
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
            'عمان', // Amman
            'زرقا', // Zarqa
            'اربد', // Irbid
            'رصیفه', // Russeifa
            'ویبه', // Wadi as-Sir
            'عجلون', // Ajloun
            'عقبه', // Aqaba
            'سلط', // Salt
            'مدبا', // Madaba
            'جرش', // Jerash
            'معان', // Ma'an
            'کرک', // Karak
            'طفیله', // Tafilah
            'عین الباشا', // Ain al-Basha
            'بیت راس', // Beit Ras
            'الرمثا', // Ar-Ramtha
            'المفرق', // Mafraq
            'سحاب', // Sahab
            'الفحیص', // Al-Fuheis
            'القویسمه', // Quwaysima
            'ابو علندا', // Abu Alanda
            'الخالدیه', // Khalidiyah
            'النصر', // An-Nasr
            'المحطه', // Al-Mahatta
            'الصریح', // As-Sarih
            'الاغوار الشمالیه', // North Jordan Valley
            'دیر علا', // Deir Alla
            'طبربور', // Tabarbour
            'الجیزه', // Al-Jizah
            'بیرین', // Birain
            'تل الاعمر', // Tell al-Ahmar
            'المناره', // Al-Manar
            'ابو نصیر', // Abu Nsair
            'الیادوده', // Al-Yadudah
            'النعیمه', // An-Na'imah
            'الشویفیه', // Ash-Shweifiyeh
            'الدوار السابع', // Dawar as-Sabi
            'طبقه فحل', // Tabaqat Fahl
            'وادی موسی', // Wadi Musa
            'العدسیه', // Al-Adsiyah
            'بیادر ویبه', // Biyader Wadi as-Sir
            'ام السماق', // Umm as-Summaq
            'الصوالحه', // As-Swalihah
            'خربه سکاکا', // Khirbet Sakaka
            'تل المنطح', // Tell al-Mantah
            'الاغوار الوسطی', // Central Jordan Valley
            'صافوط', // Safut
            'عرجان', // Arjan
            'جدیته', // Judita
        ];
    }
}
