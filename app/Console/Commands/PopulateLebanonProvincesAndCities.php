<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateLebanonProvincesAndCities extends Command
{
    protected $signature = 'populate:lebanon-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Lebanon provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Lebanon provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Lebanon country record');
            return 1;
        }

        $this->info("Using Lebanon country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Lebanon provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'لبنان')->first();

        if (!$country) {
            $this->info('Creating Lebanon country record...');
            $country = Country::create([
                'title' => 'لبنان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Lebanon)...');

        $provinceName = 'لبنان';

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

        $province = Province::where('title', 'لبنان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Lebanon province not found. Please run --only-provinces first.');
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
            'بیروت', // Beirut
            'طرابلس', // Tripoli
            'صیدا', // Sidon
            'صور', // Tyre
            'زحله', // Zahle
            'جونیه', // Jounieh
            'بعلبک', // Baalbek
            'انطلیاس', // Antelias
            'بعبدا', // Baabda
            'الشیاح', // Ash-Shiyah
            'برج البراجنه', // Burj al-Barajneh
            'عین الرمانه', // Ain ar-Rummaneh
            'حدث', // Hadath
            'سن الفیل', // Sin el Fil
            'فرن الشباک', // Furn ash-Shubbak
            'الغبیری', // Al-Ghabeiry
            'برج حمود', // Bourj Hammoud
            'فیاضیه', // Fayadiyeh
            'دیر قوبل', // Deir Qubel
            'کفرشیما', // Kafr Shima
            'العرامون', // Al-Aramoun
            'عالیه', // Aley
            'بحمدون', // Bhamdoun
            'سوق الغرب', // Souk el Gharb
            'دیر القمر', // Deir al-Qamar
            'بیت الدین', // Beiteddine
            'المختاره', // Al-Mukhtarah
            'شملان', // Shamlan
            'کرمالتوله', // Kfar Matta
            'الدامور', // Ad-Damur
            'نعمه', // Naameh
            'الدوحه', // Ad-Doha
            'الرشیدیه', // Ar-Rashidiyeh
            'نبطیه', // Nabatieh
            'مرجعیون', // Marjayoun
            'حاصبیا', // Hasbaya
            'بنت جبیل', // Bint Jbeil
            'تبنین', // Tebnine
            'قانا', // Qana
            'کفرکلا', // Kafr Kila
            'علما الشعب', // Alma ash-Shaab
            'یارون', // Yaroun
            'الهرمل', // Hermel
            'عرسال', // Arsal
            'راس بعلبک', // Ras Baalbek
            'مجدل عنجر', // Majdal Anjar
            'کسروان', // Keserwan
            'جبیل', // Byblos
            'البترون', // Batroun
            'زغرتا', // Zgharta
            'کوره', // Koura
            'دنیه', // Dinniyeh
            'بشری', // Bcharre
            'ابشروان', // Absharon
            'جزین', // Jezzine
        ];
    }
}
