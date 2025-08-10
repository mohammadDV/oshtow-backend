<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateAlgeriaProvincesAndCities extends Command
{
    protected $signature = 'populate:algeria-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Algeria provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Algeria provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Algeria country record');
            return 1;
        }

        $this->info("Using Algeria country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Algeria provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'الجزایر')->first();

        if (!$country) {
            $this->info('Creating Algeria country record...');
            $country = Country::create([
                'title' => 'الجزایر',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Algeria)...');

        $provinceName = 'الجزایر';

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

        $province = Province::where('title', 'الجزایر')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Algeria province not found. Please run --only-provinces first.');
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
            'الجزایر', // Algiers
            'وهران', // Oran
            'قسنطینه', // Constantine
            'عنابه', // Annaba
            'بلیده', // Blida
            'باتنه', // Batna
            'سطیف', // Setif
            'سیدی بلعباس', // Sidi Bel Abbès
            'بسکره', // Biskra
            'تبسه', // Tebessa
            'ورقله', // Ouargla
            'سکیکده', // Skikda
            'مستغانم', // Mostaganem
            'برج بوعریریج', // Bordj Bou Arreridj
            'بجایه', // Béjaïa
            'تلمسان', // Tlemcen
            'الاغواط', // Laghouat
            'مسیله', // M'sila
            'المدیه', // Médéa
            'معسکر', // Mascara
            'ادرار', // Adrar
            'البویره', // Bouira
            'قالمه', // Guelma
            'غردایه', // Ghardaïa
            'جیجل', // Jijel
            'سعیده', // Saïda
            'خنشله', // Khenchela
            'سوق اهراس', // Souk Ahras
            'میله', // Mila
            'عین الدفلی', // Aïn Defla
            'تیارت', // Tiaret
            'بشار', // Béchar
            'تمنراست', // Tamanrasset
            'الوادی', // El Oued
            'تندوف', // Tindouf
            'تیسمسیلت', // Tissemsilt
            'النعامه', // El Naama
            'عین تموشنت', // Aïn Témouchent
            'رلیزان', // Relizane
            'البیض', // El Bayadh
            'ایلیزی', // Illizi
            'برج باجی مختار', // Bordj Badji Mokhtar
            'اولاد جلال', // Ouled Djellal
            'بنی عباس', // Béni Abbès
            'تیمیمون', // Timimoun
            'توقرت', // Touggourt
            'جانت', // Djanet
            'المغیر', // El M'Ghair
            'منیعه', // Meniaa
            'الحضیره', // Bir el Djir
            'حاسی مسعود', // Hassi Messaoud
            'الخروب', // El Khroub
            'رویبه', // Rouiba
            'برج الکیفان', // Bordj El Kiffan
            'الحراش', // El Harrach
            'دار البیضاء', // Dar El Beida
            'الشراقه', // Cheraga
            'دالی ابراهیم', // Dely Ibrahim
            'هیدره', // Hydra
            'بیر تواته', // Bir Touta
            'باینم', // Bab Ezzouar
            'بئر مراد رایس', // Bir Mourad Raïs
        ];
    }
}