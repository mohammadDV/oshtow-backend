<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateMoroccoProvincesAndCities extends Command
{
    protected $signature = 'populate:morocco-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Morocco provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Morocco provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Morocco country record');
            return 1;
        }

        $this->info("Using Morocco country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Morocco provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'مراکش')->first();

        if (!$country) {
            $this->info('Creating Morocco country record...');
            $country = Country::create([
                'title' => 'مراکش',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Morocco)...');

        $provinceName = 'مراکش';

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

        $province = Province::where('title', 'مراکش')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Morocco province not found. Please run --only-provinces first.');
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
            'کازابلانکا', // Casablanca
            'رباط', // Rabat
            'فس', // Fes
            'مراکش', // Marrakech
            'آگادیر', // Agadir
            'مکنس', // Meknes
            'طنجه', // Tangier
            'وجده', // Oujda
            'کنیتره', // Kenitra
            'تمارا', // Temara
            'صفرو', // Sefrou
            'نادور', // Nador
            'برشید', // Berrechid
            'خریبگه', // Khouribga
            'جدیده', // El Jadida
            'تازه', // Taza
            'محمدیه', // Mohammedia
            'خمیسات', // Khemisset
            'بنی ملال', // Beni Mellal
            'العرائش', // Larache
            'الجدیده', // Al Jadida
            'تیطوان', // Tetouan
            'ایفران', // Ifrane
            'اولاد تیمه', // Ouled Teima
            'تزنیت', // Tiznit
            'تارودانت', // Taroudant
            'انزگان', // Inezgane
            'وزان', // Ouazzane
            'القصر الکبیر', // Ksar El Kebir
            'سیدی قاسم', // Sidi Kacem
            'اولاد سلام', // Ouled Saleh
            'سیدی سلیمان', // Sidi Slimane
            'ورزازات', // Ouarzazate
            'صفرو', // Sefrou
            'میدلت', // Midelt
            'ارفود', // Erfoud
            'زاگوره', // Zagora
            'رشیدیه', // Er-Rachidia
            'فکیک', // Figuig
            'جرادا', // Jerada
            'الحسیمه', // Al Hoceima
            'شفشاون', // Chefchaouen
            'وادی لاو', // Ouad Law
            'اصیله', // Asilah
            'مولای بوسلهام', // Moulay Bousselham
            'قلعه سراغنه', // Kalaat Sraghna
            'صخیرات', // Skhirat
            'مولای بو عزه', // Moulay Bouazza
            'بوزنیقه', // Bouznika
            'سلا الجدیده', // Sale
            'حد السوالم', // Had Soualem
            'بن احمد', // Ben Ahmed
            'ابن سلیمان', // Benslimane
            'ازمور', // Azemmour
            'الواقیه', // Lwalida
            'خمیسیات', // Khumasiyat
            'بوقنادل', // Bouknadel
            'المعذر', // Al Mu'adhar
            'زرهون', // Zerhoun
            'علال التازی', // Allal Tazi
            'بولمان', // Boulman
            'سوق الثلاثاء', // Souk El Tleta
            'سوق الاربعاء', // Souk El Arba
        ];
    }
}
