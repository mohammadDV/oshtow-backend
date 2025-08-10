<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateAzerbaijanProvincesAndCities extends Command
{
    protected $signature = 'populate:azerbaijan-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Azerbaijan provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Azerbaijan provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Azerbaijan country record');
            return 1;
        }

        $this->info("Using Azerbaijan country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Azerbaijan provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'آذربایجان')->first();

        if (!$country) {
            $this->info('Creating Azerbaijan country record...');
            $country = Country::create([
                'title' => 'آذربایجان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Azerbaijan)...');

        $provinceName = 'آذربایجان';

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

        $province = Province::where('title', 'آذربایجان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Azerbaijan province not found. Please run --only-provinces first.');
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
            'باکو', // Baku
            'گنجه', // Ganja
            'سومقاییت', // Sumqayit
            'شکی', // Sheki
            'گیانجا', // Gyanja
            'لنکران', // Lankaran
            'نافتالان', // Naftalan
            'منگچویر', // Mingachevir
            'ییولاخ', // Yevlakh
            'شماخی', // Shamakhi
            'قوبا', // Quba
            'خچماز', // Khachmaz
            'ساتلی', // Saatli
            'شوشا', // Shusha
            'آغجه‌بدی', // Agjabadi
            'بیلسور', // Bilasuvar
            'برده', // Barda
            'ایمیشلی', // Imishli
            'نخچیوان', // Nakhchivan
            'شاروخ', // Sharur
            'اردبیل', // Ordubad
            'جولفا', // Julfa
            'شهبوز', // Shahbuz
            'کنگرلی', // Kangarli
            'صدرک', // Sadarak
            'قازاخ', // Qazakh
            'تووز', // Tovuz
            'شمکیر', // Shamkir
            'گویگول', // Goygol
            'داشکسن', // Dashkasan
            'گدبای', // Gadabay
            'اسمایل آبادی', // Ismayilli
            'آغسو', // Agsu
            'گوبوستان', // Gobustan
            'سیازان', // Siyazan
            'دویچی', // Divichi
            'شبران', // Shabran
            'قوسار', // Qusar
            'خیزی', // Khizi
            'اوغوز', // Oguz
            'گبله', // Gabala
            'لاگیچ', // Lahij
            'ساموخ', // Samukh
            'آغداش', // Agdash
            'گویچای', // Goychay
            'کورده‌میر', // Kurdamir
            'زردب', // Zardab
            'اوجار', // Ujar
            'کیوردامیر', // Kərdəmir
            'سالیان', // Salyan
            'نفت‌چاله', // Neftchala
            'جلیل‌آباد', // Jalilabad
            'لریجان', // Lerik
            'یاردیملی', // Yardymli
            'مسیلی', // Masalli
            'آستارا', // Astara
        ];
    }
}