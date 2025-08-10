<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateNorthKoreaProvincesAndCities extends Command
{
    protected $signature = 'populate:north-korea-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate North Korea provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting North Korea provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create North Korea country record');
            return 1;
        }

        $this->info("Using North Korea country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('North Korea provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'کره شمالی')->first();

        if (!$country) {
            $this->info('Creating North Korea country record...');
            $country = Country::create([
                'title' => 'کره شمالی',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (North Korea)...');

        $provinceName = 'کره شمالی';

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

        $province = Province::where('title', 'کره شمالی')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('North Korea province not found. Please run --only-provinces first.');
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
            'پیونگ‌یانگ', // Pyongyang
            'چونگ‌جین', // Chongjin
            'نامپو', // Nampo
            'وونسان', // Wonsan
            'هامهونگ', // Hamhung
            'سینوایجو', // Sinuiju
            'هاجو', // Haeju
            'کانگگه', // Kanggye
            'سانگیونگ', // Sariwon
            'کیچون', // Gicheok
            'کوسونگ', // Kosan
            'توکچون', // Tokchon
            'آنجو', // Anju
            'هیسان', // Hyesan
            'کیمچک', // Kimchaek
            'آیوجی', // Aoji
            'مواسان', // Musan
            'هونام', // Hoeryong
            'چونسان', // Chunsan
            'چونگدان', // Chongdan
            'کیچون', // Kechon
            'هامژونگ', // Hamjung
            'یونگبائونگ', // Yongbyung
            'پویئون', // Puyon
            'سونچون', // Sonchon
            'اونجین', // Uijin
            'مونچون', // Monchon
            'کیونگسونگ', // Kyongsong
            'هواچون', // Hwachon
            'یونگوداری', // Yongudong
            'مایسان', // Maisan
            'سودونگ', // Sodong
            'چانگدان', // Changdan
            'پاکچون', // Pakchon
            'کیم‌جونگ‌سوک', // Kimjongsuk
            'ریویون', // Riwon
            'کونسان', // Gosan
            'یونسا', // Yonsa
            'مایبنگ‌سان', // Maebongsan
            'هوچانگ', // Hochang
            'چانگین', // Changjin
            'پوچونگ', // Puchong
            'کیل‌جو', // Kilju
            'مانگیونگ‌دا', // Mangyongdae
        ];
    }
}