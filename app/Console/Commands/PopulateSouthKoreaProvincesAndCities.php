<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateSouthKoreaProvincesAndCities extends Command
{
    protected $signature = 'populate:south-korea-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate South Korea provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting South Korea provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create South Korea country record');
            return 1;
        }

        $this->info("Using South Korea country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('South Korea provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'کره جنوبی')->first();

        if (!$country) {
            $this->info('Creating South Korea country record...');
            $country = Country::create([
                'title' => 'کره جنوبی',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (South Korea)...');

        $provinceName = 'کره جنوبی';

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

        $province = Province::where('title', 'کره جنوبی')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('South Korea province not found. Please run --only-provinces first.');
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
            'سئول', // Seoul
            'بوسان', // Busan
            'اینچئون', // Incheon
            'دایگو', // Daegu
            'دایجئون', // Daejeon
            'گوانگجو', // Gwangju
            'سووان', // Suwon
            'اولسان', // Ulsan
            'چانگوون', // Changwon
            'گویانگ', // Goyang
            'یونگین', // Yongin
            'سئونگنام', // Seongnam
            'بوچئون', // Bucheon
            'آنسان', // Ansan
            'چئونان', // Cheonan
            'آنیانگ', // Anyang
            'پوهانگ', // Pohang
            'جئونجو', // Jeonju
            'چهون', // Cheongju
            'چونچئون', // Chuncheon
            'کیم‌هه', // Gimhae
            'آسان', // Asan
            'هوانام', // Hwaseong
            'پیانگتک', // Pyeongtaek
            'سیهنگ', // Siheung
            'کیمپو', // Gimpo
            'جه‌جو', // Jeju
            'کونج', // Guri
            'آنیونگ', // Yangju
            'مینا', // Masan
            'یوسو', // Yeosu
            'چیکسان', // Iksan
            'ورانگ', // Wonju
            'گونپو', // Gunpo
            'اوری', // Osan
            'هایانگ', // Hayang
            'کانگنئونگ', // Gangneung
            'کیمچئون', // Gimcheon
            'شنچئونج', // Seongcheongju
            'موکپو', // Mokpo
            'سانجو', // Sangju
            'سوگیپو', // Seogipo
            'گومی', // Gumi
            'کانگ‌ریونگ', // Gangryung
            'دانگجین', // Dangjin
            'وانجو', // Wanju
            'مینتونگ', // Milyang
            'تونگ‌یئونگ', // Tongyeong
            'نونسان', // Nonsan
            'کیونگ‌جو', // Gyeongju
            'رییل', // Yeongcheon
            'میریانگ', // Miryang
            'نامیانگجو', // Namyangju
            'کوری', // Guri
            'دونگ‌دوچئون', // Dongducheon
            'پوچئون', // Pocheon
            'کواچئون', // Gwacheon
        ];
    }
}