<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateBelgiumProvincesAndCities extends Command
{
    protected $signature = 'populate:belgium-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Belgium provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Belgium provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Belgium country record');
            return 1;
        }

        $this->info("Using Belgium country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Belgium provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'بلژیک')->first();

        if (!$country) {
            $this->info('Creating Belgium country record...');
            $country = Country::create([
                'title' => 'بلژیک',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Belgium)...');

        $provinceName = 'بلژیک';

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

        $province = Province::where('title', 'بلژیک')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Belgium province not found. Please run --only-provinces first.');
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
            'بروکسل', // Brussels
            'آنتورپ', // Antwerp
            'گنت', // Ghent
            'شارلوروا', // Charleroi
            'لوژ', // Liège
            'بروژ', // Bruges
            'نامور', // Namur
            'لووین', // Leuven
            'مونس', // Mons
            'آالست', // Aalst
            'لا لووییر', // La Louvière
            'کورتری', // Kortrijk
            'هاسلت', // Hasselt
            'سن نیکلاس', // Sint-Niklaas
            'اوستند', // Ostend
            'تورنای', // Tournai
            'گنک', // Genk
            'ساین ترویدن', // Sint-Truiden
            'روزلار', // Roeselare
            'موسکرون', // Mouscron
            'ویرویرز', // Verviers
            'دیندر', // Dendermonde
            'بگین', // Beghin
            'آرلون', // Arlon
            'توژین', // Taguine
            'میشلن', // Mechelen
            'بواین', // Boaine
            'آندنه', // Andenne
            'وترین', // Waterloo
            'سورت پیر', // Soure Pierre
            'ایپر', // Ypres
            'آتن', // Ath
            'سواسون', // Seison
            'ایوی', // Eupen
            'تیلت', // Tielt
            'دیون', // Diest
            'هی', // Huy
            'بلوریت', // Blankenberge
            'زوتگم', // Zottegem
            'لومل', // Lommel
            'ونن', // Waregem
            'مکین', // Menen
            'شین آر لی', // Chaudfontaine
            'بوری', // Bourey
            'کنوک لو زوت', // Knokke-le-Zoute
            'بوردین', // Beringen
            'دیلسن استن', // Dilsen-Stokkem
        ];
    }
}