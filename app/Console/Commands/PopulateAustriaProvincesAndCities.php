<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateAustriaProvincesAndCities extends Command
{
    protected $signature = 'populate:austria-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Austria provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Austria provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Austria country record');
            return 1;
        }

        $this->info("Using Austria country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Austria provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'اتریش')->first();

        if (!$country) {
            $this->info('Creating Austria country record...');
            $country = Country::create([
                'title' => 'اتریش',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Austria)...');

        $provinceName = 'اتریش';

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

        $province = Province::where('title', 'اتریش')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Austria province not found. Please run --only-provinces first.');
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
            'وین', // Vienna
            'گراتس', // Graz
            'لینتس', // Linz
            'زالتسبورگ', // Salzburg
            'اینسبروک', // Innsbruck
            'کلاگنفورت', // Klagenfurt
            'ویلش', // Villach
            'ویلدون', // Wels
            'سن پلتن', // Sankt Pölten
            'دورنبیرن', // Dornbirn
            'شتایر', // Steyr
            'وینر نویشتات', // Wiener Neustadt
            'فلدکیرش', // Feldkirch
            'برگتس', // Bregenz
            'لئوبن', // Leoben
            'کرمس آن در دونا', // Krems an der Donau
            'تراون', // Traun
            'کاپفنبرگ', // Kapfenberg
            'میدلینگ', // Mödling
            'لوستنائو', // Lustenau
            'بادن', // Baden
            'وولفسبرگ', // Wolfsberg
            'کلوسترنویبورگ', // Klosterneuburg
            'هالین', // Hallein
            'برک آن در مور', // Bruck an der Mur
            'لیونینگ', // Leonding
            'وینر', // Weiner
            'کوفشتاین', // Kufstein
            'امشیت', // Amstetten
            'بد ایشل', // Bad Ischl
            'ماترسبورگ', // Mattersburg
            'اایزنشتات', // Eisenstadt
            'انفزآن در انز', // Enns an der Enns
            'شولادیمنگ', // Schladming
            'گروندلزه', // Gmunden
            'بیشوفشوفن', // Bischofshofen
            'وورگل', // Wörgl
            'پرندورف', // Parndorf
            'گنگس', // Götzis
            'اد راویش', // Radkersburg
            'وایز', // Waidhofen
            'گلوگنیتس', // Gloggnitz
            'گیسینگ', // Giesing
            'هورن', // Horn
            'آلتنمارکت', // Altenmarkt
        ];
    }
}