<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateVietnamProvincesAndCities extends Command
{
    protected $signature = 'populate:vietnam-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Vietnam provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Vietnam provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Vietnam country record');
            return 1;
        }

        $this->info("Using Vietnam country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Vietnam provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'ویتنام')->first();

        if (!$country) {
            $this->info('Creating Vietnam country record...');
            $country = Country::create([
                'title' => 'ویتنام',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Vietnam)...');

        $provinceName = 'ویتنام';

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

        $province = Province::where('title', 'ویتنام')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Vietnam province not found. Please run --only-provinces first.');
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
            'هانوی', // Hanoi
            'هوشی‌مین', // Ho Chi Minh City
            'هایفونگ', // Haiphong
            'دانانگ', // Da Nang
            'کانتو', // Can Tho
            'آنگیانگ', // An Giang
            'بیننداک', // Binh Dinh
            'کایان', // Ca Mau
            'دونگ نای', // Dong Nai
            'هاگیانگ', // Ha Giang
            'هالونگ', // Ha Long
            'هانام', // Ha Nam
            'هاتینه', // Ha Tinh
            'هاییڈونگ', // Hai Duong
            'ہائوژیانگ', // Hau Giang
            'هوا بینہ', // Hoa Binh
            'هونگین', // Hung Yen
            'کانهوا', // Khanh Hoa
            'کینجیانگ', // Kien Giang
            'کونتوم', // Kon Tum
            'لائیچوو', // Lai Chau
            'لامدونگ', // Lam Dong
            'لانگسون', // Lang Son
            'لائوکای', // Lao Cai
            'لانگ‌دونگ', // Long An
            'نامدینہ', // Nam Dinh
            'نگهان', // Nghe An
            'نینہ بینہ', // Ninh Binh
            'نینہ تھوآن', // Ninh Thuan
            'فوتہو', // Phu Tho
            'فویین', // Phu Yen
            'کوانگ بینہ', // Quang Binh
            'کوانگ نام', // Quang Nam
            'کوانگ نگائی', // Quang Ngai
            'کوانگ نینہ', // Quang Ninh
            'کوانگ تری', // Quang Tri
            'سوک ترانگ', // Soc Trang
            'سون لا', // Son La
            'تائے نینہ', // Tay Ninh
            'تھائی بینہ', // Thai Binh
            'تھائی نگوین', // Thai Nguyen
            'تھانہ هوا', // Thanh Hoa
            'تھوا تھیین ہوے', // Thua Thien Hue
            'تین جیانگ', // Tien Giang
            'ترا وینہ', // Tra Vinh
            'توین کوانگ', // Tuyen Quang
            'وینہ لونگ', // Vinh Long
            'وینہ فوک', // Vinh Phuc
            'ین بائی', // Yen Bai
            'بانکان', // Bac Kan
            'بانگیانگ', // Bac Giang
            'بک لیو', // Bac Lieu
            'بک نینہ', // Bac Ninh
        ];
    }
}