<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateThailandProvincesAndCities extends Command
{
    protected $signature = 'populate:thailand-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Thailand provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Thailand provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Thailand country record');
            return 1;
        }

        $this->info("Using Thailand country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Thailand provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'تایلند')->first();

        if (!$country) {
            $this->info('Creating Thailand country record...');
            $country = Country::create([
                'title' => 'تایلند',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Thailand)...');

        $provinceName = 'تایلند';

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

        $province = Province::where('title', 'تایلند')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Thailand province not found. Please run --only-provinces first.');
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
            'بانکوک', // Bangkok
            'سامت پراکان', // Samut Prakan
            'مکان', // Mueang Nonthaburi
            'اودون تانی', // Udon Thani
            'چون بوری', // Chon Buri
            'ناکون راچسیما', // Nakhon Ratchasima
            'چیانگ مای', // Chiang Mai
            'هات یای', // Hat Yai
            'اودون', // Udon
            'پاکنام', // Pak Kret
            'سی راچا', // Si Racha
            'پاراگ وئون بوری', // Phra Pradaeng
            'لام‌چباگ', // Laem Chabang
            'ناکون سی تامارات', // Nakhon Si Thammarat
            'خون کائن', // Khon Kaen
            'ناکون پاتوم', // Nakhon Pathom
            'اوبون راچتانی', // Ubon Ratchathani
            'رانگ سیت', // Rangsit
            'سورت تانی', // Surat Thani
            'راچبوری', // Ratchaburi
            'لام‌فاک چی', // Lampang
            'چانتابوری', // Chanthaburi
            'لووی', // Loei
            'فوکت', // Phuket
            'تونبوری', // Thonburi
            'پاتایا', // Pattaya
            'ناکون نایوک', // Nakhon Nayok
            'پیتسانولوک', // Phitsanulok
            'سامت ساکون', // Samut Sakhon
            'ساراگ بوری', // Saraburi
            'پتچابون', // Phetchabun
            'کانچانابوری', // Kanchanaburi
            'پرچین بوری', // Prachin Buri
            'مای ماسولم', // Mae Rim
            'چیانگ رای', // Chiang Rai
            'سوپان بوری', // Suphan Buri
            'پهانگ نگا', // Phang Nga
            'کرابی', // Krabi
            'تراگ', // Trat
            'ناراتیوات', // Narathiwat
            'پتانی', // Pattani
            'یالا', // Yala
            'سونگکلا', // Songkhla
            'ستول', // Satun
            'فاگ نگا', // Phangnga
            'ناکون سی تامارات', // Nakhon Si Thammarat
            'سورین', // Surin
            'بوری رام', // Buriram
            'نونگ خای', // Nong Khai
            'ساکون ناکون', // Sakon Nakhon
        ];
    }
}