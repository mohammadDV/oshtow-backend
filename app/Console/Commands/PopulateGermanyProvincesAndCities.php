<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateGermanyProvincesAndCities extends Command
{
    protected $signature = 'populate:germany-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Germany provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Germany provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Germany country record');
            return 1;
        }

        $this->info("Using Germany country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Germany provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'آلمان')->first();

        if (!$country) {
            $this->info('Creating Germany country record...');
            $country = Country::create([
                'title' => 'آلمان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Germany)...');

        $provinceName = 'آلمان';

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

        $province = Province::where('title', 'آلمان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Germany province not found. Please run --only-provinces first.');
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
            'برلین', // Berlin
            'هامبورگ', // Hamburg
            'مونیخ', // Munich
            'کلن', // Cologne
            'فرانکفورت', // Frankfurt
            'اشتوتگارت', // Stuttgart
            'دوسلدورف', // Düsseldorf
            'دورتموند', // Dortmund
            'اسن', // Essen
            'لایپزیگ', // Leipzig
            'برمن', // Bremen
            'درسدن', // Dresden
            'هانوور', // Hannover
            'نورنبرگ', // Nuremberg
            'دویسبورگ', // Duisburg
            'بوخوم', // Bochum
            'ووپرتال', // Wuppertal
            'بیله‌فلد', // Bielefeld
            'بون', // Bonn
            'مونستر', // Münster
            'کارلس‌روهه', // Karlsruhe
            'مانهایم', // Mannheim
            'آوگزبورگ', // Augsburg
            'ویسبادن', // Wiesbaden
            'گلزن‌کیرشن', // Gelsenkirchen
            'مونشن‌گلادباخ', // Mönchengladbach
            'براونشوایگ', // Braunschweig
            'چمنیتس', // Chemnitz
            'کیل', // Kiel
            'آخن', // Aachen
            'هاله', // Halle
            'مگدبورگ', // Magdeburg
            'فرایبورگ', // Freiburg
            'کریه‌فلد', // Krefeld
            'لوبک', // Lübeck
            'اوبرهاوزن', // Oberhausen
            'ارفورت', // Erfurt
            'مایز', // Mainz
            'روستوک', // Rostock
            'کاسل', // Kassel
            'هاگن', // Hagen
            'پوتسدام', // Potsdam
            'زارلند', // Saarland
            'لودویگس‌هافن', // Ludwigshafen
            'اولدنبورگ', // Oldenburg
            'لورکوزن', // Leverkusen
            'اوزنابروک', // Osnabrück
            'زولینگن', // Solingen
            'هایدلبرگ', // Heidelberg
            'هرنه', // Herne
            'نویس', // Neuss
            'دارمشتات', // Darmstadt
            'ورتسبورگ', // Würzburg
            'اولم', // Ulm
            'گوتینگن', // Göttingen
            'ولفسبورگ', // Wolfsburg
            'رکلینگ‌هاوزن', // Recklinghausen
            'اینگولشتات', // Ingolstadt
            'هایلبرون', // Heilbronn
            'اوفن‌باخ', // Offenbach
            'فورث', // Fürth
            'آشافن‌بورگ', // Aschaffenburg
            'برمرهافن', // Bremerhaven
            'تریر', // Trier
            'ویلهلمس‌هافن', // Wilhelmshaven
            'کوتبوس', // Cottbus
            'کمنیتس', // Chemnitz
            'اایزناخ', // Eisenach
            'فلنسبورگ', // Flensburg
            'شوورین', // Schwerin
        ];
    }
}