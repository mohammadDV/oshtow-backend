<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateSaudiArabiaProvincesAndCities extends Command
{
    protected $signature = 'populate:saudi-arabia-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Saudi Arabia provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Saudi Arabia provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Saudi Arabia country record');
            return 1;
        }

        $this->info("Using Saudi Arabia country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Saudi Arabia provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'عربستان سعودی')->first();

        if (!$country) {
            $this->info('Creating Saudi Arabia country record...');
            $country = Country::create([
                'title' => 'عربستان سعودی',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Saudi Arabia)...');

        $provinceName = 'عربستان سعودی';

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

        $province = Province::where('title', 'عربستان سعودی')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Saudi Arabia province not found. Please run --only-provinces first.');
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
            'ریاض', // Riyadh
            'جده', // Jeddah
            'مکه', // Mecca
            'مدینه', // Medina
            'دمام', // Dammam
            'خبر', // Khobar
            'تبوک', // Tabuk
            'حائل', // Hail
            'برانگ', // Buraidah
            'تائف', // Taif
            'ابها', // Abha
            'خمیس مشیط', // Khamis Mushait
            'نجران', // Najran
            'جازان', // Jazan
            'ینبع', // Yanbu
            'قطیف', // Qatif
            'الاحساء', // Al-Ahsa
            'عنیزه', // Unaizah
            'عرعر', // Arar
            'سکاکا', // Sakakah
            'طریف', // Turaif
            'رفحا', // Rafha
            'القریات', // Al-Qurayyat
            'دوادمی', // Dawadmi
            'الافلاج', // Al-Aflaj
            'الخرج', // Al-Kharj
            'المجمعه', // Al-Majmaah
            'الزلفی', // Az-Zulfi
            'شقراء', // Shaqra
            'القوایعیه', // Al-Quwayiyah
            'حوطه بنی تمیم', // Hotat Bani Tamim
            'المزاحمیه', // Al-Muzahimiyah
            'ثادق', // Thadiq
            'رماح', // Rumah
            'الغاط', // Al-Ghat
            'ضرما', // Durma
            'المویه', // Al-Muwayh
            'السلیل', // As-Sulayyil
            'وادی الدواسر', // Wadi ad-Dawasir
            'بیشه', // Bishah
            'الباحه', // Al-Baha
            'المخواه', // Al-Mikhwah
            'قلوه', // Qilwah
            'العقیق', // Al-Aqiq
            'المندق', // Al-Mandaq
            'بلجرشی', // Baljurashi
            'محایل', // Muhayil
            'صبیا', // Sabya
            'ابو عریش', // Abu Arish
            'صامطه', // Samtah
            'العیدابی', // Al-Idabi
            'الدرب', // Ad-Darb
            'فرسان', // Farasan
            'الطوال', // At-Tuwal
            'الحرث', // Al-Harith
            'الداعر', // Ad-Dair
            'الریث', // Ar-Rayth
            'بیش', // Baysh
            'الشقیق', // Ash-Shaqiq
            'احد المسارحه', // Ahad al-Masarihah
            'ضمد', // Damad
            'الشقیری', // Ash-Shuqairi
            'العارضه', // Al-Aridah
            'رجال المع', // Rijal Almaa
            'محایل عسیر', // Muhayil Asir
        ];
    }
}
