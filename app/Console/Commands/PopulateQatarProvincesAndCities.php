<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateQatarProvincesAndCities extends Command
{
    protected $signature = 'populate:qatar-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Qatar provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Qatar provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Qatar country record');
            return 1;
        }

        $this->info("Using Qatar country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Qatar provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'قطر')->first();

        if (!$country) {
            $this->info('Creating Qatar country record...');
            $country = Country::create([
                'title' => 'قطر',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Qatar)...');

        $provinceName = 'قطر';

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

        $province = Province::where('title', 'قطر')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Qatar province not found. Please run --only-provinces first.');
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
            'الدوحه', // Doha
            'الریان', // Al Rayyan
            'امم صلال', // Umm Salal
            'الخور', // Al Khor
            'الوکره', // Al Wakrah
            'دخان', // Dukhan
            'مدینه خلیفه', // Madinat Khalifa
            'الدعین', // Al Daayen
            'الثمامه', // Al Thumama
            'الشمال', // Al Shamal
            'الکعبان', // Al Kaaaban
            'راس لفان', // Ras Laffan
            'مسیعید', // Mesaieed
            'الزبارة', // Al Zubarah
            'الغویریه', // Al Ghuwariyah
            'الجمیلیه', // Al Jumayliyah
            'الخریطیات', // Al Kharitiyat
            'فوارت', // Fuwayrit
            'سیمیسمه', // Simaisma
            'الکور', // Al Kur
            'مرخیه', // Markhiya
            'ابوظلوف', // Abu Dhalouf
            'الریان الجدید', // Al Rayyan Al Jadeed
            'لوسیل', // Lusail
            'المطار الجدید', // Al Matar Al Jadeed
            'درینه', // Dureina
            'الجیان', // Al Jayan
            'معیذر', // Muaither
            'النصر', // Al Nasr
            'غرافه', // Gharafa
            'الهلال', // Al Hilal
            'الدحیل', // Al Duhail
            'الصدف', // Al Sadd
            'العزیزیه', // Al Aziziyah
            'المعمورة', // Al Mamoura
            'النفل', // Al Nafl
            'مدینة حمد', // Madinat Hamad
            'عین خالد', // Ain Khaled
            'ابوهامور', // Abu Hamour
            'الطیاره', // Al Tayer
            'وادی السیل', // Wadi Al Sail
            'امم قرن', // Umm Qarn
            'الرکیه', // Al Ruqayya
            'الجودة', // Al Juda
            'امم صلال محمد', // Umm Salal Mohammed
            'السیلیه', // Al Sailiya
            'الشحانیه', // Al Shahaniya
            'الراکة', // Al Rakah
            'البداع', // Al Bidaa
            'الختم', // Al Khatam
            'الفیحاء', // Al Fayha
            'الظعاین', // Al Dhaeen
            'الگراده', // Al Grada
            'روضة راشد', // Rawdat Rashid
            'نفطه', // Nafta
            'الاصبع', // Al Isba
            'الکوردة', // Al Kurda
            'المرونة', // Al Murwana
            'العریش', // Al Areesh
            'الوجبه', // Al Wajba
            'امم البرکة', // Umm Al Baraka
        ];
    }
}
