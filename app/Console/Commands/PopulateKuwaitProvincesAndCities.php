<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateKuwaitProvincesAndCities extends Command
{
    protected $signature = 'populate:kuwait-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Kuwait provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Kuwait provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Kuwait country record');
            return 1;
        }

        $this->info("Using Kuwait country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Kuwait provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'کویت')->first();

        if (!$country) {
            $this->info('Creating Kuwait country record...');
            $country = Country::create([
                'title' => 'کویت',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Kuwait)...');

        $provinceName = 'کویت';

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

        $province = Province::where('title', 'کویت')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Kuwait province not found. Please run --only-provinces first.');
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
            'کویت سیتی', // Kuwait City
            'سلمیه', // Salmiya
            'حولی', // Hawalli
            'جهراء', // Jahra
            'الفروانیه', // Farwaniya
            'الاحمدی', // Ahmadi
            'مبارک الکبیر', // Mubarak al-Kabeer
            'الفحیحیل', // Fahaheel
            'الصباحیه', // Sabahiya
            'الخیران', // Khiran
            'الضجیج', // Dajeej
            'القرین', // Qaryat al-Giran
            'العدان', // Adan
            'أبرق خیطان', // Abraq Khaitan
            'جلیب الشیوخ', // Jleeb Al-Shuyoukh
            'الریقی', // Riggae
            'بیان', // Bayan
            'مشرف', // Mishref
            'الصدیق', // Siddeeq
            'الزهراء', // Zahra
            'العقیله', // Aqila
            'الفنطاس', // Fintas
            'الروضه', // Rawdha
            'الخالدیه', // Khalidiya
            'قرطبه', // Qortoba
            'النهضه', // Nahda
            'الیرموک', // Yarmouk
            'المیره', // Mubärakiyah
            'الرابیه', // Rabiya
            'الحساویه', // Hassawiyah
            'الفیصلیه', // Faisaliya
            'ضاحیه صباح ناصر', // Sabah an-Nasir
            'کیفان', // Kaifan
            'الواحه', // Waha
            'أم الهیمان', // Umm al-Haiman
            'الرقه', // Riqqa
            'الدوحه', // Doha
            'الوفره', // Wafra
            'النویصیب', // Nuwaiseeb
            'الدمنه', // Damanah
            'الکوت', // Kaot
            'صدر', // Sadr
            'الروضتین', // Rawdatain
            'المطلاع', // Mutla
            'تیماء', // Tayma
            'السره', // Surrah
            'مدینه صباح احمد البحریه', // Sabah Ahmad Sea City
        ];
    }
}
