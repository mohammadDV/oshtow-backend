<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateUKProvincesAndCities extends Command
{
    protected $signature = 'populate:uk-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate UK provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting UK provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create UK country record');
            return 1;
        }

        $this->info("Using UK country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('UK provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'انگلستان')->first();

        if (!$country) {
            $this->info('Creating UK country record...');
            $country = Country::create([
                'title' => 'انگلستان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (UK)...');

        $provinceName = 'انگلستان';

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

        $province = Province::where('title', 'انگلستان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('UK province not found. Please run --only-provinces first.');
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
            'لندن', // London
            'بیرمینگهام', // Birmingham
            'لیورپول', // Liverpool
            'منچستر', // Manchester
            'شفیلد', // Sheffield
            'برستول', // Bristol
            'گلاسگو', // Glasgow
            'لیدز', // Leeds
            'ادینبورگ', // Edinburgh
            'کاردیف', // Cardiff
            'لستر', // Leicester
            'کاونتری', // Coventry
            'بلفاست', // Belfast
            'نوتینگهام', // Nottingham
            'هال', // Hull
            'نیوکاسل', // Newcastle
            'استوک', // Stoke-on-Trent
            'ساوتهمپتون', // Southampton
            'پورتسموث', // Portsmouth
            'پریستون', // Preston
            'آبردین', // Aberdeen
            'سواتسی', // Swansea
            'ساندرلند', // Sunderland
            'میدلزبرو', // Middlesbrough
            'نورویچ', // Norwich
            'آکسفورد', // Oxford
            'کمبریج', // Cambridge
            'یورک', // York
            'باث', // Bath
            'کانتربری', // Canterbury
            'تشستر', // Chester
            'اکستر', // Exeter
            'گلاستر', // Gloucester
            'لنکاستر', // Lancaster
            'لینکن', // Lincoln
            'سالزبری', // Salisbury
            'ویندزور', // Windsor
            'داردی', // Derby
            'پترباروگ', // Peterborough
            'رادینگ', // Reading
            'واروکیک', // Warwick
            'بانگور', // Bangor
            'اینورنس', // Inverness
            'استرلینگ', // Stirling
            'سنت آندریوز', // St Andrews
            'نیوپورت', // Newport
            'وکینگ', // Woking
            'هرفورد', // Hereford
            'بکینگهام', // Buckingham
        ];
    }
}