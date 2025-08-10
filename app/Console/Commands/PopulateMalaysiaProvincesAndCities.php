<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateMalaysiaProvincesAndCities extends Command
{
    protected $signature = 'populate:malaysia-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Malaysia provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Malaysia provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Malaysia country record');
            return 1;
        }

        $this->info("Using Malaysia country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Malaysia provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'مالزی')->first();

        if (!$country) {
            $this->info('Creating Malaysia country record...');
            $country = Country::create([
                'title' => 'مالزی',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Malaysia)...');

        $provinceName = 'مالزی';

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

        $province = Province::where('title', 'مالزی')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Malaysia province not found. Please run --only-provinces first.');
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
            'کوالالامپور', // Kuala Lumpur
            'جورج تاون', // George Town
            'ایپوه', // Ipoh
            'شاه علم', // Shah Alam
            'پتالینگ جایا', // Petaling Jaya
            'کلانگ', // Klang
            'جوهور باهرو', // Johor Bahru
            'سوبانگ جایا', // Subang Jaya
            'کوچینگ', // Kuching
            'کوتا کینابالو', // Kota Kinabalu
            'ملاکا', // Malacca City
            'آلور ستار', // Alor Setar
            'سرمبان', // Seremban
            'کوانتان', // Kuantan
            'کوالا ترنگانو', // Kuala Terengganu
            'تایپینگ', // Taiping
            'کوتا بهارو', // Kota Bharu
            'کانگار', // Kangar
            'مری', // Miri
            'سیبو', // Sibu
            'بینتولو', // Bintulu
            'سندکان', // Sandakan
            'تاواو', // Tawau
            'لاهاد داتو', // Lahad Datu
            'سیپیتانگ', // Sipitang
            'کینینگاو', // Keningau
            'بویفورت', // Beaufort
            'ران بحر', // Ranau
            'سمپورنا', // Semporna
            'کونگ', // Kunak
            'تنوم', // Tenom
            'پتالینگ', // Petaling
            'کاجانگ', // Kajang
            'آمپانگ', // Ampang
            'چراس', // Cheras
            'سوبانگ', // Subang
            'دامانسارا', // Damansara
            'بندر سری پتالینگ', // Bandar Sri Petaling
            'بنگسار', // Bangsar
            'کیپونگ', // Kepong
            'بریک فیلدز', // Brickfields
            'بوکیت بینتانگ', // Bukit Bintang
            'موناش', // Monash
            'آمپانگ جایا', // Ampang Jaya
            'چراس جایا', // Cheras Jaya
            'پوچونگ', // Puchong
            'سردانگ', // Serdang
            'سپانگ', // Sepang
            'کاجانگ', // Kajang
            'بندر بارو بانگی', // Bandar Baru Bangi
            'پوترا جایا', // Putrajaya
            'سایبر جایا', // Cyberjaya
            'بتو کیوس', // Batu Caves
            'سونگای بولوه', // Sungai Buloh
            'راوانگ', // Rawang
            'سلانگور', // Selangor
            'کوالا سلانگور', // Kuala Selangor
            'سابک برنام', // Sabak Bernam
            'تانجونگ کارانگ', // Tanjung Karang
            'کوالا لانگات', // Kuala Langat
            'بندر', // Bander
            'گمبانگ', // Gombak
            'هولو سلانگور', // Hulu Selangor
            'درین', // Darin
            'سیتیا وان', // Setia Alam
            'شاه علم ۲', // Shah Alam 2
        ];
    }
}
