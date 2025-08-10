<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateUkraineProvincesAndCities extends Command
{
    protected $signature = 'populate:ukraine-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Ukraine provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Ukraine provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Ukraine country record');
            return 1;
        }

        $this->info("Using Ukraine country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Ukraine provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'اوکراین')->first();

        if (!$country) {
            $this->info('Creating Ukraine country record...');
            $country = Country::create([
                'title' => 'اوکراین',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Ukraine)...');

        $provinceName = 'اوکراین';

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

        $province = Province::where('title', 'اوکراین')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Ukraine province not found. Please run --only-provinces first.');
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
            'کیف', // Kyiv
            'خارکیو', // Kharkiv
            'اودسا', // Odesa
            'دنیپرو', // Dnipro
            'دونتسک', // Donetsk
            'زاپورژیا', // Zaporizhzhia
            'لوگانسک', // Luhansk
            'کریویری گ', // Kryvyi Rih
            'میکولایو', // Mykolaiv
            'ماریپل', // Mariupol
            'لووف', // Lviv
            'ماکیفکا', // Makiivka
            'وینیتسا', // Vinnytsia
            'خرسون', // Kherson
            'پولتاوا', // Poltava
            'چرنیگیو', // Chernihiv
            'چرکاسی', // Cherkasy
            'ژیتومیر', // Zhytomyr
            'سوما', // Sumy
            'خملنیتسکی', // Khmelnytskyi
            'چرنیوتسی', // Chernivtsi
            'رونه', // Rivne
            'دنیپرودژرژینسک', // Dniprodzerzhynsk
            'کرامتورسک', // Kramatorsk
            'ترنوپل', // Ternopil
            'کیروگراد', // Kropyvnytskyi
            'ایوانو فرانکووسک', // Ivano-Frankivsk
            'لوتسک', // Lutsk
            'سیفروپل', // Simferopol
            'اوژگورود', // Uzhhorod
            'بلا تسرکوا', // Bila Tserkva
            'پاولگراد', // Pavlohrad
            'کاخوکا', // Kakhovka
            'آلچوفسک', // Alchevsk
            'لیسچنسک', // Lysychansk
            'کادیفکا', // Kadiyivka
            'سلاویانسک', // Sloviansk
            'بردیانسک', // Berdiansk
            'نووموسکوفسک', // Novomoskovsk
            'دروگبیچ', // Drohobych
            'مولیف', // Melitopol
            'نیکوپل', // Nikopol
            'ایزمایل', // Izmail
            'اواله', // Korolyev
            'کوستیانتینیوکا', // Kostiantynivka
            'کونوتپ', // Konotop
            'شوستکا', // Shostka
            'اومان', // Uman
            'آلتشفسک', // Alchevsk
        ];
    }
}