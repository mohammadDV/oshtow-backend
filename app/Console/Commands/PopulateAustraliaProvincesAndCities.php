<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateAustraliaProvincesAndCities extends Command
{
    protected $signature = 'populate:australia-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Australia provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Australia provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Australia country record');
            return 1;
        }

        $this->info("Using Australia country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Australia provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'استرالیا')->first();

        if (!$country) {
            $this->info('Creating Australia country record...');
            $country = Country::create([
                'title' => 'استرالیا',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Australia)...');

        $provinceName = 'استرالیا';

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

        $province = Province::where('title', 'استرالیا')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Australia province not found. Please run --only-provinces first.');
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
            'سیدنی', // Sydney
            'ملبورن', // Melbourne
            'بریزبن', // Brisbane
            'پرت', // Perth
            'آدلاید', // Adelaide
            'کانبرا', // Canberra
            'داروین', // Darwin
            'هوبارت', // Hobart
            'گلد کوست', // Gold Coast
            'نیوکاسل', // Newcastle
            'ولونگونگ', // Wollongong
            'سانشاین کوست', // Sunshine Coast
            'جیلونگ', // Geelong
            'تاونزویل', // Townsville
            'کیرنز', // Cairns
            'توومبا', // Toowoomba
            'بالارات', // Ballarat
            'بندیگو', // Bendigo
            'آلبوری', // Albury
            'وودونگا', // Wodonga
            'لانستون', // Launceston
            'مکای', // Mackay
            'راک‌همپتون', // Rockhampton
            'باندابرگ', // Bundaberg
            'کوئینزتاون', // Queenstown
            'آلیس اسپرینگز', // Alice Springs
            'کالگورلی', // Kalgoorlie
            'بروم', // Broome
            'پورت هدلند', // Port Hedland
            'کینز', // Karratha
            'جرالدتون', // Geraldton
            'بانی', // Bunbury
            'وورنپول', // Warrnambool
            'شپارتون', // Shepparton
            'میلدورا', // Mildura
            'وگا وگا', // Wagga Wagga
            'اورنج', // Orange
            'دوبو', // Dubbo
            'آرمیدیل', // Armidale
            'تامورت', // Tamworth
            'کوفس هاربر', // Coffs Harbour
            'پورت مکواری', // Port Macquarie
            'لایسمور', // Lismore
            'کینگاروی', // Kingaroy
            'ماریبورو', // Maryborough
            'هروی بای', // Hervey Bay
            'گیمپی', // Gympie
            'نومبا', // Nambour
            'کابولچر', // Caboolture
            'لوگان', // Logan
            'ایپسویچ', // Ipswich
            'توومبا', // Toowoomba
            'وارویک', // Warwick
            'استنثورپ', // Stanthorpe
            'گویندا', // Goondiwindi
            'دالبی', // Dalby
            'کینگاروی', // Kingaroy
            'ماریبورو', // Maryborough
            'هروی بای', // Hervey Bay
            'گیمپی', // Gympie
            'نومبا', // Nambour
            'کابولچر', // Caboolture
            'لوگان', // Logan
            'ایپسویچ', // Ipswich
            'توومبا', // Toowoomba
            'وارویک', // Warwick
            'استنثورپ', // Stanthorpe
            'گویندا', // Goondiwindi
            'دالبی', // Dalby
            'کینگاروی', // Kingaroy
            'ماریبورو', // Maryborough
            'هروی بای', // Hervey Bay
            'گیمپی', // Gympie
            'نومبا', // Nambour
            'کابولچر', // Caboolture
            'لوگان', // Logan
            'ایپسویچ', // Ipswich
            'توومبا', // Toowoomba
            'وارویک', // Warwick
            'استنثورپ', // Stanthorpe
            'گویندا', // Goondiwindi
            'دالبی', // Dalby
        ];
    }
}
