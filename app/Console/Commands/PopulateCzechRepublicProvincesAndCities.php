<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateCzechRepublicProvincesAndCities extends Command
{
    protected $signature = 'populate:czech-republic-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Czech Republic provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Czech Republic provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Czech Republic country record');
            return 1;
        }

        $this->info("Using Czech Republic country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Czech Republic provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'جمهوری چک')->first();

        if (!$country) {
            $this->info('Creating Czech Republic country record...');
            $country = Country::create([
                'title' => 'جمهوری چک',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Czech Republic)...');

        $provinceName = 'جمهوری چک';

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

        $province = Province::where('title', 'جمهوری چک')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Czech Republic province not found. Please run --only-provinces first.');
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
            'پراگ', // Prague
            'برنو', // Brno
            'اوستراوا', // Ostrava
            'پلزن', // Plzen
            'لیبرتس', // Liberec
            'اولوموتس', // Olomouc
            'بودویسه', // Budweiss
            'هرادتس کرالووه', // Hradec Králové
            'اوستی ناد لابم', // Ústí nad Labem
            'پاردوبیتسه', // Pardubice
            'زلین', // Zlín
            'هاوژیوف', // Havířov
            'کلادنو', // Kladno
            'ماست آلبرچت', // Most Alberchice
            'اوپاوا', // Opava
            'فرادک', // Frydek-Mistek
            'کارویناه', // Karviná
            'یابلونتس ناد نیسو', // Jablonec nad Nisou
            'ملادا بولسلاو', // Mladá Boleslav
            'پروستیجوف', // Prostějov
            'تشسکه بودوجیتسه', // České Budějovice
            'تابور', // Tábor
            'زنویمو', // Znojmo
            'تپلیتسه', // Teplice
            'یهلاوا', // Jihlava
            'دیچین', // Děčín
            'کاستی', // Karlový Vary
            'هومتوف', // Chomutov
            'پژبرام', // Příbram
            'تروتنو', // Trutnov
            'نووی یچین', // Nový Jičín
            'ترهووه سویسه', // Trhové Sviny
            'بلانسکو', // Blansko
            'پرزروف', // Prerov
            'اونانو ناد لابم', // Uherské Hradiště
            'کدان', // Kadan
            'ریخنوف ناد کنسو', // Rychnov nad Kněžnou
            'بنشوف', // Benešov
            'چرونی کوستلتس', // Cervený Kostelec
            'آش', // Aš
            'وسلی ناد لوزنیتسی', // Veseli nad Luznici
            'لیتومیرژیتسه', // Litoměřice
            'سوکولوف', // Sokolov
            'وودنانی', // Vodnany
            'کوتنا هورا', // Kutná Hora
            'کولین', // Kolin
            'نیمبورک', // Nymburk
            'راکونیک', // Rakovník
        ];
    }
}