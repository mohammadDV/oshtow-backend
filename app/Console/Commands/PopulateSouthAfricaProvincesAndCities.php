<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateSouthAfricaProvincesAndCities extends Command
{
    protected $signature = 'populate:south-africa-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate South Africa provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting South Africa provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create South Africa country record');
            return 1;
        }

        $this->info("Using South Africa country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('South Africa provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'آفریقای جنوبی')->first();

        if (!$country) {
            $this->info('Creating South Africa country record...');
            $country = Country::create([
                'title' => 'آفریقای جنوبی',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (South Africa)...');

        $provinceName = 'آفریقای جنوبی';

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

        $province = Province::where('title', 'آفریقای جنوبی')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('South Africa province not found. Please run --only-provinces first.');
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
            'کیپ تاون', // Cape Town
            'ژوهانسبورگ', // Johannesburg
            'دربان', // Durban
            'پرتوریا', // Pretoria
            'پورت الیزابت', // Port Elizabeth
            'وین بای', // Wynberg
            'سندتون', // Sandton
            'بنونی', // Benoni
            'تامبیسا', // Tembisa
            'پیترماریتسبورگ', // Pietermaritzburg
            'ولکام', // Welkom
            'سولتا', // Soweto
            'بلومفونتین', // Bloemfontein
            'کیمبرلی', // Kimberley
            'پولوکوانه', // Polokwane
            'نلسپروئیت', // Nelspruit
            'راستنبورگ', // Rustenburg
            'جرج', // George
            'ایست لندن', // East London
            'موسل بای', // Muizenberg
            'کلار وود', // Klerksdorp
            'پوتچفستروم', // Potchefstroom
            'امرسفورت', // Ermelo
            'بریتس', // Brits
            'ویتبنک', // Witbank
            'کراد', // Kroonstad
            'آپینگتون', // Upington
            'آوتدشورن', // Oudtshoorn
            'سلدانها', // Saldanha
            'والکر بای', // Walker Bay
            'مالمسبری', // Malmesbury
            'پارل', // Paarl
            'ولینگتون', // Wellington
            'ورسستر', // Worcester
            'اولزن', // Oelsen
            'کرست', // Kroonstad
            'باتورست', // Bathurst
            'استلنبوش', // Stellenbosch
            'هرمانوس', // Hermanus
            'کنیسنا', // Knysna
            'پلتنبرگ بای', // Plettenberg Bay
            'ساوث باند', // Southbound
            'کراف رینت', // Graaf-Reinet
            'کولسبرگ', // Colesberg
            'میدلبورگ', // Middleburg
            'گراهامستون', // Grahamstown
            'کینگ ویلیامز تاون', // King Williams Town
            'بیسهوک', // Bisho
            'مویسل', // Mdantsane
            'اوکوها', // Idutywa
            'بتر ورث', // Butterworth
            'متاته', // Mthatha
            'ام تاتا', // Umtata
            'کوکستاد', // Kokstad
            'لیدی اسمیت', // Ladysmith
            'نیوکاسل', // Newcastle
            'فری استیت', // Vryheid
            'اولتراشت', // Utrecht
            'استانگر', // Estcourt
            'فلکسبورگ', // Volksrust
            'داندی', // Dundee
            'ریچارد بای', // Richards Bay
            'امپانگنی', // Empangeni
            'هوویک', // Howick
        ];
    }
}
