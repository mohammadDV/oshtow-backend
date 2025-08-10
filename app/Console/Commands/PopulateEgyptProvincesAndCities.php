<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateEgyptProvincesAndCities extends Command
{
    protected $signature = 'populate:egypt-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Egypt provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Egypt provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Egypt country record');
            return 1;
        }

        $this->info("Using Egypt country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Egypt provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'مصر')->first();

        if (!$country) {
            $this->info('Creating Egypt country record...');
            $country = Country::create([
                'title' => 'مصر',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Egypt)...');

        $provinceName = 'مصر';

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

        $province = Province::where('title', 'مصر')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Egypt province not found. Please run --only-provinces first.');
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
            'قاهره', // Cairo
            'اسکندریه', // Alexandria
            'جیزه', // Giza
            'شبرا الخیمه', // Shubra El Kheima
            'بورسعید', // Port Said
            'السویس', // Suez
            'الاقصر', // Luxor
            'المنصوره', // Mansoura
            'الطنطا', // Tanta
            'اسیوط', // Asyut
            'اسماعیلیه', // Ismailia
            'فیوم', // Faiyum
            'الزقازیق', // Zagazig
            'دمیاط', // Damietta
            'اسوان', // Aswan
            'المنیا', // Minya
            'دمنهور', // Damanhur
            'بنی سویف', // Beni Suef
            'قنا', // Qena
            'سوهاج', // Sohag
            'الغردقه', // Hurghada
            'السیوکه', // Siwa
            'رفح', // Rafah
            'العریش', // Al-Arish
            'مرسی مطروح', // Marsa Matruh
            'شرم الشیخ', // Sharm El Sheikh
            'دهب', // Dahab
            'الوادی الجدید', // New Valley
            'بنی مزار', // Beni Mazar
            'الحلل', // Mallawi
            'دیروط', // Dirout
            'منفلوط', // Manfalout
            'ابو تیج', // Abu Tig
            'الطیه الشریف', // Tema al-Sharif
            'الخانقه', // Khankah
            'بلبیس', // Bilbeis
            'العاشر من رمضان', // 10th of Ramadan City
            'السادس من اکتوبر', // 6th of October City
            'النوباریه', // El Nubaria
            'کفر الدوار', // Kafr El Dawwar
            'رشید', // Rosetta
            'البرلس', // Burullus
            'المحله الکبری', // El Mahalla El Kubra
            'کفر الشیخ', // Kafr El Sheikh
            'دسوق', // Desouk
            'فوه', // Fuwa
            'مطوبس', // Motobas
            'سیدی سالم', // Sidi Salem
            'ادکو', // Idku
            'ابو حمص', // Abu Homs
            'ایتای البارود', // Itay El Barud
            'شبین الکوم', // Shebin El Kom
            'منوف', // Menouf
            'ساقیه ابو شعره', // Sadat City
            'قویسنا', // Quesna
            'اشمون', // Ashmun
            'تلا', // Tala
            'السنطه', // El Santa
            'کوم حماده', // Kom Hamada
            'الحامول', // El Hamoul
            'مرکز قلین', // Qalyub
            'بنها', // Benha
            'شبین القناطر', // Shebin El Qanater
            'طوخ', // Tokh
            'کفر شکر', // Kafr Shukr
        ];
    }
}
