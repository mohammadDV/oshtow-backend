<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateJapanProvincesAndCities extends Command
{
    protected $signature = 'populate:japan-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Japan provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Japan provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Japan country record');
            return 1;
        }

        $this->info("Using Japan country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Japan provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'ژاپن')->first();

        if (!$country) {
            $this->info('Creating Japan country record...');
            $country = Country::create([
                'title' => 'ژاپن',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Japan)...');

        $provinceName = 'ژاپن';

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

        $province = Province::where('title', 'ژاپن')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Japan province not found. Please run --only-provinces first.');
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
            'توکیو', // Tokyo
            'یوکوهاما', // Yokohama
            'اوساکا', // Osaka
            'ناگویا', // Nagoya
            'ساپورو', // Sapporo
            'فوکوئوکا', // Fukuoka
            'کوبه', // Kobe
            'کیوتو', // Kyoto
            'کاواساکی', // Kawasaki
            'سایتاما', // Saitama
            'هیروشیما', // Hiroshima
            'سندای', // Sendai
            'کیتاکیوشو', // Kitakyushu
            'چیبا', // Chiba
            'ساکای', // Sakai
            'نیگاتا', // Niigata
            'هامامتسو', // Hamamatsu
            'کوماموتو', // Kumamoto
            'ساگامیهارا', // Sagamihara
            'شیزوکا', // Shizuoka
            'اوکایاما', // Okayama
            'کاگوشیما', // Kagoshima
            'هاچیوجی', // Hachioji
            'هیمجی', // Himeji
            'ماتسویاما', // Matsuyama
            'اوتسو', // Otsu
            'توویاما', // Toyama
            'کاناتاوا', // Kanazawa
            'یوکوسوکا', // Yokosuka
            'اوئیتا', // Oita
            'نارا', // Nara
            'توشیما', // Toshima
            'فوجیساوا', // Fujisawa
            'فوکویاما', // Fukuyama
            'توکاماتسو', // Takamatsu
            'ماچیدا', // Machida
            'مینامی', // Minami
            'کوریبایاشی', // Kuriyashashi
            'ایواکی', // Iwaki
            'کوچی', // Kochi
            'کاسوگای', // Kasugai
            'کاوائوچی', // Kawauchi
            'سوگینامی', // Suginami
            'نراشینو', // Narashino
            'توریده', // Toride
            'مورانو', // Murano
            'میتو', // Mito
            'آساهیکاوا', // Asahikawa
            'آکیتا', // Akita
            'ناها', // Naha
            'اویاما', // Oyama
            'سوزوکا', // Suzuka
            'شینیوکوهاما', // Shin-Yokohama
            'جوئتسو', // Joetsu
            'یامائو', // Yamatokoriyama
            'آتسوگی', // Atsugi
            'ایچیکاوا', // Ichikawa
            'ماتسوئودو', // Matsudo
            'فونابشی', // Funabashi
        ];
    }
}