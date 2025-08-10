<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateOmanProvincesAndCities extends Command
{
    protected $signature = 'populate:oman-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Oman provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Oman provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Oman country record');
            return 1;
        }

        $this->info("Using Oman country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Oman provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'عمان')->first();

        if (!$country) {
            $this->info('Creating Oman country record...');
            $country = Country::create([
                'title' => 'عمان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Oman)...');

        $provinceName = 'عمان';

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

        $province = Province::where('title', 'عمان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Oman province not found. Please run --only-provinces first.');
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
            'مسقط', // Muscat
            'صلاله', // Salalah
            'صحار', // Sohar
            'نزوی', // Nizwa
            'صور', // Sur
            'الرستاق', // Rustaq
            'بهلاء', // Bahla
            'بدبد', // Bidbid
            'مطرح', // Matrah
            'عبری', // Ibri
            'عزا', // Izki
            'روی', // Ruwi
            'بحیره', // Buhairah
            'قریات', // Qurayyat
            'بوشر', // Bausher
            'مسفاه', // Misfah
            'البیمان', // Al-Haiman
            'ضنک', // Dhank
            'مبیله', // Mabela
            'الخوض', // Al-Khoudh
            'الأنصب', // Al-Ansab
            'السیب', // As-Sib
            'العذیبه', // Al-Azaiba
            'الغبره', // Al-Ghubrah
            'المعبیله', // Al-Maabilah
            'المعولی', // Al-Maawali
            'صحم', // Saham
            'لوی', // Liwa
            'صوقره', // Suqarah
            'شناص', // Shinas
            'طاقه', // Taqah
            'رخیوت', // Rakhyut
            'ضلکوت', // Dhalkut
            'اشکذره', // Ashkithara
            'مقشن', // Maqshan
            'حاسک', // Hasik
            'طوی اعتیر', // Tawi Ateer
            'صدح', // Sadah
            'منح', // Manh
            'الکامل والوافی', // Al-Kamil wa al-Wafi
            'جعلان بنی بوحسن', // Ja'alan Bani Bu Hassan
            'جعلان بنی بوعلی', // Ja'alan Bani Bu Ali
            'مصیره', // Masirah
            'الدقم', // Duqm
            'هیما', // Hayma
            'وادی بنی خالد', // Wadi Bani Khalid
            'بدیه', // Bidiyah
            'منطقه الکمیل', // Mantiqat al-Kamil
            'عبادت', // Ibadat
            'مدحا', // Madha
            'النجیره', // Najeerah
        ];
    }
}
