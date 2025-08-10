<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateNewZealandProvincesAndCities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:new-zealand-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate New Zealand provinces and cities with proper relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting New Zealand provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create New Zealand country record');
            return 1;
        }

        $this->info("Using New Zealand country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('New Zealand provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'نیوزیلند')->first();

        if (!$country) {
            $this->info('Creating New Zealand country record...');
            $country = Country::create([
                'title' => 'نیوزیلند',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (New Zealand)...');

        $provinceName = 'نیوزیلند';

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

        $province = Province::where('title', 'نیوزیلند')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('New Zealand province not found. Please run --only-provinces first.');
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
            'اوکلند', // Auckland
            'ولینگتون', // Wellington
            'کرایست‌چرچ', // Christchurch
            'همیلتون', // Hamilton
            'تائورانگا', // Tauranga
            'نپیر', // Napier
            'پالمرستون نورث', // Palmerston North
            'روتوروا', // Rotorua
            'نیو پلیموث', // New Plymouth
            'وانگانوی', // Whanganui
            'نلسون', // Nelson
            'اینورکارگیل', // Invercargill
            'دوندین', // Dunedin
            'تیمارو', // Timaru
            'گیسبورن', // Gisborne
            'هستینگز', // Hastings
            'وانگانوی', // Whanganui
            'نیو پلیموث', // New Plymouth
            'روتوروا', // Rotorua
            'تائورانگا', // Tauranga
            'همیلتون', // Hamilton
            'کرایست‌چرچ', // Christchurch
            'ولینگتون', // Wellington
            'اوکلند', // Auckland
            'پاراپاراومو', // Paraparaumu
            'کاپیتی', // Kapiti
            'لوور هات', // Lower Hutt
            'اپر هات', // Upper Hutt
            'پوریروا', // Porirua
            'کاپیتی آیلند', // Kapiti Island
            'ماناکائو', // Manakau
            'پاپاکورا', // Papakura
            'فرانکلین', // Franklin
            'رودنی', // Rodney
            'کائپارا', // Kaipara
            'فار نورث', // Far North
            'ویپو', // Whangarei
            'کائیکوهه', // Kaikohe
            'کریکری', // Kerikeri
            'پایهیا', // Paihia
            'راسل', // Russell
            'کاوکا', // Kawakawa
            'موآنگا', // Moerewa
            'اوپونونی', // Opononi
            'اوماپیر', // Omapere
            'کائیتایا', // Kaitaia
            'آهیپارا', // Ahipara
            'مانگونویی', // Mangonui
            'کوهوکا', // Kohukohu
            'هوکیانگا', // Hokianga
            'کائیکوهه', // Kaikohe
            'کریکری', // Kerikeri
            'پایهیا', // Paihia
            'راسل', // Russell
            'کاوکا', // Kawakawa
            'موآنگا', // Moerewa
            'اوپونونی', // Opononi
            'اوماپیر', // Omapere
            'کائیتایا', // Kaitaia
            'آهیپارا', // Ahipara
            'مانگونویی', // Mangonui
            'کوهوکا', // Kohukohu
            'هوکیانگا', // Hokianga
        ];
    }
}
