<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateIndonesiaProvincesAndCities extends Command
{
    protected $signature = 'populate:indonesia-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Indonesia provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Indonesia provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Indonesia country record');
            return 1;
        }

        $this->info("Using Indonesia country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Indonesia provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'اندونزی')->first();

        if (!$country) {
            $this->info('Creating Indonesia country record...');
            $country = Country::create([
                'title' => 'اندونزی',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Indonesia)...');

        $provinceName = 'اندونزی';

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

        $province = Province::where('title', 'اندونزی')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Indonesia province not found. Please run --only-provinces first.');
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
            'جاکارتا', // Jakarta
            'سورابایا', // Surabaya
            'مدان', // Medan
            'بکاسی', // Bekasi
            'بندونگ', // Bandung
            'پالمبانگ', // Palembang
            'تانگرانگ', // Tangerang
            'دپوک', // Depok
            'سمارانگ', // Semarang
            'مکاسار', // Makassar
            'باتام', // Batam
            'بندر لامپونگ', // Bandar Lampung
            'بوگور', // Bogor
            'پکان‌بارو', // Pekanbaru
            'پادانگ', // Padang
            'ملانگ', // Malang
            'سایمارانگ', // Samarinda
            'دنپاسار', // Denpasar
            'تاسیک‌مالایا', // Tasikmalaya
            'بانجارماسین', // Banjarmasin
            'پونتیاناک', // Pontianak
            'چیریبون', // Cirebon
            'بایان تری', // Balikpapan
            'جامبی', // Jambi
            'سوراکارتا', // Surakarta
            'یوگیاکارتا', // Yogyakarta
            'مانادو', // Manado
            'آمبون', // Ambon
            'کوپانگ', // Kupang
            'جای‌پورا', // Jayapura
            'کندری', // Kendari
            'پالو', // Palu
            'گورونتالو', // Gorontalo
            'منتونگ', // Mataram
            'بیما', // Bima
            'سی‌نگارایا', // Singaraja
            'بوکیت‌تینگگی', // Bukittinggi
            'بات‌پانچانگ', // Tanjungpinang
            'گونونگ‌سیتولی', // Gunungsitoli
            'سابانگ', // Sabang
            'لهوک‌سوماوه', // Lhokseumawe
            'لانگسا', // Langsa
            'مولابوه', // Meulaboh
            'سیدی‌کالانگ', // Sidikalang
            'پماتانگ‌سیانتار', // Pematangsiantar
            'تبینگ‌تینگگی', // Tebingtinggi
            'بینجای', // Binjai
            'لوبوک‌پاکام', // Lubukpakam
            'پرایا', // Praya
            'تیتی', // Titi
        ];
    }
}