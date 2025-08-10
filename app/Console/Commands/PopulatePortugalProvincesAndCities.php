<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulatePortugalProvincesAndCities extends Command
{
    protected $signature = 'populate:portugal-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Portugal provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Portugal provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Portugal country record');
            return 1;
        }

        $this->info("Using Portugal country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Portugal provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'پرتغال')->first();

        if (!$country) {
            $this->info('Creating Portugal country record...');
            $country = Country::create([
                'title' => 'پرتغال',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Portugal)...');

        $provinceName = 'پرتغال';

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

        $province = Province::where('title', 'پرتغال')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Portugal province not found. Please run --only-provinces first.');
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
            'لیسبون', // Lisbon
            'پورتو', // Porto
            'آمادورا', // Amadora
            'براگا', // Braga
            'ستوبال', // Setúbal
            'کویمبرا', // Coimbra
            'فونشال', // Funchal
            'کاسکائیس', // Cascais
            'ویلا نووا د گایا', // Vila Nova de Gaia
            'لویریا', // Leiria
            'آویرو', // Aveiro
            'مایا', // Maia
            'ماتوزینهوس', // Matosinhos
            'گوندومار', // Gondomar
            'ریو تینتو', // Rio Tinto
            'باررییرو', // Barreiro
            'آلمادا', // Almada
            'سانتارم', // Santarém
            'گوآردا', // Guarda
            'ویانا دو کاستلو', // Viana do Castelo
            'فاروس', // Faro
            'پورتالگر', // Portalegre
            'ایوورا', // Évora
            'آنگرا دو هروئیسمو', // Angra do Heroísmo
            'پونتا دلگادا', // Ponta Delgada
            'براگانسا', // Bragança
            'ویلا ریل', // Vila Real
            'ویزئو', // Viseu
            'کاستلو برانکو', // Castelo Branco
            'بژا', // Beja
            'پورتیمائو', // Portimão
            'لاگوش', // Lagos
            'تاویرا', // Tavira
            'الیش', // Elvas
            'اوبیدوش', // Óbidos
            'ناراره', // Nazaré
            'بتالها', // Batalha
            'سینترا', // Sintra
            'اسپینهو', // Espinho
            'آویرو', // Aveiro
            'لامگو', // Lamego
            'گیمارائیش', // Guimarães
            'چاوش', // Chaves
            'مونچیقه', // Monchique
            'ایلهاوو', // Ílhavo
            'سوزل', // Sousel
            'مونتیژو', // Montijo
            'توماز', // Tomar
            'کالداش د راینها', // Caldas da Rainha
        ];
    }
}