<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulatePolandProvincesAndCities extends Command
{
    protected $signature = 'populate:poland-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Poland provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Poland provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Poland country record');
            return 1;
        }

        $this->info("Using Poland country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Poland provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'لهستان')->first();

        if (!$country) {
            $this->info('Creating Poland country record...');
            $country = Country::create([
                'title' => 'لهستان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Poland)...');

        $provinceName = 'لهستان';

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

        $province = Province::where('title', 'لهستان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Poland province not found. Please run --only-provinces first.');
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
            'ورشو', // Warsaw
            'کراکوف', // Krakow
            'وودج', // Łódź
            'وروتساو', // Wrocław
            'پوزنان', // Poznań
            'گدانسک', // Gdańsk
            'شتتین', // Szczecin
            'بیدگوشچ', // Bydgoszcz
            'لوبلین', // Lublin
            'کاتوویتسه', // Katowice
            'بیالیستوک', // Białystok
            'گدینیا', // Gdynia
            'چنستوخووا', // Częstochowa
            'رادوم', // Radom
            'سوسنووتس', // Sosnowiec
            'توروین', // Toruń
            'کیلتسه', // Kielce
            'گلیویتسه', // Gliwice
            'زابژه', // Zabrze
            'بیتوم', // Bytom
            'اولشتین', // Olsztyn
            'بیلسکو بیالا', // Bielsko-Biała
            'ژشوف', // Rzeszów
            'روده', // Ruda Śląska
            'اوپوله', // Opole
            'ویوتسواویک', // Włocławek
            'زیلونا گورا', // Zielona Góra
            'دابروا گورنیچا', // Dąbrowa Górnicza
            'والبژیخ', // Wałbrzych
            'خوژوف', // Chorzów
            'پیوترکوف تریبونالسکی', // Piotrków Trybunalski
            'پواتسک', // Płock
            'اکواپونیک', // Koszalin
            'لگنیتسا', // Legnica
            'اتشین', // Słupsk
            'خالم', // Chełm
            'نووی سانچ', // Nowy Sącz
            'لشنو', // Leszno
            'گروژیتس', // Grudziądz
            'یلنیا گورا', // Jelenia Góra
            'نووگارد', // Nowy Targ
            'کنین', // Konin
            'گنیزنو', // Gniezno
            'استارگارد', // Stargard
            'سوالکی', // Suwałki
            'اوستروو ویلکوپولسکی', // Ostrów Wielkopolski
            'پیلا', // Piła
            'استاراچوویتسه', // Starachowice
            'میلتس', // Mielec
            'اینووروتساو', // Inowrocław
            'لومژا', // Łomża
        ];
    }
}