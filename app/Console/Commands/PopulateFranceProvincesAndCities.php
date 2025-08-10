<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateFranceProvincesAndCities extends Command
{
    protected $signature = 'populate:france-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate France provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting France provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create France country record');
            return 1;
        }

        $this->info("Using France country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('France provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'فرانسه')->first();

        if (!$country) {
            $this->info('Creating France country record...');
            $country = Country::create([
                'title' => 'فرانسه',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (France)...');

        $provinceName = 'فرانسه';

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

        $province = Province::where('title', 'فرانسه')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('France province not found. Please run --only-provinces first.');
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
            'پاریس', // Paris
            'مارسی', // Marseille
            'لیون', // Lyon
            'تولوز', // Toulouse
            'نیس', // Nice
            'نانت', // Nantes
            'مونپلیه', // Montpellier
            'استراسبورگ', // Strasbourg
            'بوردو', // Bordeaux
            'لیل', // Lille
            'رن', // Rennes
            'رایمز', // Reims
            'تورها', // Tours
            'سن‌اتین', // Saint-Étienne
            'تولون', // Toulon
            'گرونوبل', // Grenoble
            'آنژه', // Angers
            'نیم', // Nîmes
            'ویلوربان', // Villeurbanne
            'کلرمون فران', // Clermont-Ferrand
            'لو هاور', // Le Havre
            'آمیان', // Amiens
            'لیموژ', // Limoges
            'بزانسون', // Besançon
            'مولوز', // Mulhouse
            'بو', // Pau
            'رووان', // Rouen
            'کان', // Caen
            'آرژانتی', // Argenteuil
            'مونترویل', // Montreuil
            'نانسی', // Nancy
            'مالمیزون', // Malmaison
            'اورلئان', // Orléans
            'بولون', // Boulogne
            'دیژون', // Dijon
            'لو مان', // Le Mans
            'نانتر', // Nanterre
            'آکس آن پرووانس', // Aix-en-Provence
            'بتون', // Beton
            'آنسی', // Annecy
            'پرپینیان', // Perpignan
            'بولونیه', // Boulogne-Billancourt
            'روبه', // Roubaix
            'تورکوئن', // Tourcoing
            'کریتی', // Créteil
            'آویون', // Avignon
            'ورسای', // Versailles
            'بایون', // Bayonne
            'کالا', // Calais
            'دونکرک', // Dunkerque
            'پواتیه', // Poitiers
            'مرسدز', // Merced
            'سیتروئن', // Citroen
            'لورین', // Lorient
            'بورژ', // Bourges
            'شربورگ', // Cherbourg
            'لا رشل', // La Rochelle
            'سارسل', // Sarcelles
            'اولریون', // Aubervilliers
        ];
    }
}