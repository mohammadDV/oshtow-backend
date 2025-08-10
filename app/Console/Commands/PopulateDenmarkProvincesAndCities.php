<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateDenmarkProvincesAndCities extends Command
{
    protected $signature = 'populate:denmark-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Denmark provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Denmark provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Denmark country record');
            return 1;
        }

        $this->info("Using Denmark country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Denmark provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'دانمارک')->first();

        if (!$country) {
            $this->info('Creating Denmark country record...');
            $country = Country::create([
                'title' => 'دانمارک',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Denmark)...');

        $provinceName = 'دانمارک';

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

        $province = Province::where('title', 'دانمارک')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Denmark province not found. Please run --only-provinces first.');
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
            'کپنهاگ', // Copenhagen
            'آرهوس', // Aarhus
            'اودنسه', // Odense
            'آلبورگ', // Aalborg
            'اسبیرگ', // Esbjerg
            'راندرز', // Randers
            'کولدینگ', // Kolding
            'هرسلو', // Horsens
            'وایله', // Vejle
            'رسکیلده', // Roskilde
            'نایستد', // Naestved
            'فردریشیا', // Fredericia
            'ویبورگ', // Viborg
            'کوگه', // Køge
            'سیلکبورگ', // Silkeborg
            'هولستبرو', // Holstebro
            'سوندربورگ', // Sønderborg
            'هیرینگ', // Herning
            'هلسینگور', // Helsingør
            'گروو', // Greve
            'فردریکسبرگ', // Frederiksberg
            'تاستروپ', // Taastrup
            'ایسهوی', // Ishøj
            'سولرود', // Solrød
            'آلبرتسلوند', // Albertslund
            'هرلو', // Herlev
            'رودووره', // Rødovre
            'والبی', // Valby
            'گلوستروپ', // Glostrup
            'براندبی', // Brøndby
            'هویدووره', // Hvidovre
            'دراگور', // Dragør
            'تارنبی', // Tårnby
            'کاستروپ راوکسل', // Kastrup-Rauxel
            'لینگبی', // Lyngby
            'هیلرود', // Hillerød
            'بیرکرود', // Birkerød
            'روسکیلده', // Roskilde
            'سوروه', // Sorø
            'هولبائک', // Holbæk
            'کاروندبورگ', // Kalundborg
            'سلگلسه', // Slagelse
            'نایکوبینگ فالستر', // Nykøbing Falster
        ];
    }
}