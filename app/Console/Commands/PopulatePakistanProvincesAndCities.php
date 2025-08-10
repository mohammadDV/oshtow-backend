<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulatePakistanProvincesAndCities extends Command
{
    protected $signature = 'populate:pakistan-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Pakistan provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Pakistan provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Pakistan country record');
            return 1;
        }

        $this->info("Using Pakistan country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Pakistan provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'پاکستان')->first();

        if (!$country) {
            $this->info('Creating Pakistan country record...');
            $country = Country::create([
                'title' => 'پاکستان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Pakistan)...');

        $provinceName = 'پاکستان';

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

        $province = Province::where('title', 'پاکستان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Pakistan province not found. Please run --only-provinces first.');
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
            'کراچی', // Karachi
            'لاہور', // Lahore
            'فیصل آباد', // Faisalabad
            'راولپنڈی', // Rawalpindi
            'ملتان', // Multan
            'حیدرآباد', // Hyderabad
            'گوجرانوالہ', // Gujranwala
            'پشاور', // Peshawar
            'کوئٹہ', // Quetta
            'اسلام آباد', // Islamabad
            'سرگودھا', // Sargodha
            'سیالکوٹ', // Sialkot
            'بہاولپور', // Bahawalpur
            'سکھر', // Sukkur
            'جھنگ', // Jhang
            'شیخوپورہ', // Sheikhupura
            'میرپور کھاس', // Mirpur Khas
            'رحیم یار خان', // Rahim Yar Khan
            'گجرات', // Gujrat
            'کاسور', // Kasur
            'ہری پور', // Haripur
            'حافظ آباد', // Hafizabad
            'صادق آباد', // Sadiqabad
            'میرپور', // Mirpur
            'نوابشاہ', // Nawabshah
            'چنیوٹ', // Chiniot
            'کمالیہ', // Kamalia
            'مردان', // Mardan
            'مینگورہ', // Mingora
            'ابوٹ آباد', // Abbottabad
            'مولوی بازار', // Muridke
            'ڈسکہ', // Daska
            'صحیوال', // Sahiwal
            'اوکاڑہ', // Okara
            'ودی', // Wah
            'ڈیرہ غازی خان', // Dera Ghazi Khan
            'کوٹلی', // Kotli
            'لاڑکانہ', // Larkana
            'کنری', // Kohat
            'بنوں', // Bannu
            'لکی مروت', // Lakki Marwat
            'گلگت', // Gilgit
            'سکردو', // Skardu
            'ٹربیلا', // Tarbela
            'ٹانک', // Tank
            'ڈیرہ اسماعیل خان', // Dera Ismail Khan
        ];
    }
}
