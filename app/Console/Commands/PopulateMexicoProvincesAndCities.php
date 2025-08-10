<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateMexicoProvincesAndCities extends Command
{
    protected $signature = 'populate:mexico-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Mexico provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Mexico provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Mexico country record');
            return 1;
        }

        $this->info("Using Mexico country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Mexico provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'مکزیک')->first();

        if (!$country) {
            $this->info('Creating Mexico country record...');
            $country = Country::create([
                'title' => 'مکزیک',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Mexico)...');

        $provinceName = 'مکزیک';

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

        $province = Province::where('title', 'مکزیک')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Mexico province not found. Please run --only-provinces first.');
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
            'مکزیکو سیتی', // Mexico City
            'گوادالاجارا', // Guadalajara
            'مونتری', // Monterrey
            'پوبلا', // Puebla
            'تیخوانا', // Tijuana
            'لیون', // León
            'خوآرز', // Juárez
            'توریون', // Torreón
            'کرتارو', // Querétaro
            'چیواوا', // Chihuahua
            'آگواسکالینتیس', // Aguascalientes
            'مریدا', // Mérida
            'میکسیکالی', // Mexicali
            'کولیاکان', // Culiacán
            'آکاپولکو', // Acapulco
            'سان لوئیس پوتوسی', // San Luis Potosí
            'تولوکا', // Toluca
            'مورلیا', // Morelia
            'تامپیکو', // Tampico
            'چیمالواکان', // Chimalhuacán
            'نائوکالپان', // Naucalpan
            'زاپوپان', // Zapopan
            'نزاوالکویوتل', // Nezahualcóyotl
            'کنکون', // Cancún
            'ورا کروز', // Veracruz
            'اکاپینگولا', // Acapulco
            'تلانپانتلا', // Tlalnepantla
            'گستاوو مادرو', // Gustavo A. Madero
            'سان نیکولاس دس گارساس', // San Nicolás de los Garzas
            'پوبلا د زاراگوزا', // Puebla de Zaragoza
            'تیجوانا', // Tijuana
            'تونالا', // Tonalá
            'اکاتزینگو', // Ecatepec
            'گوادالوپه', // Guadalupe
            'اورتلان', // Apodaca
            'آتسکپوتسالکو', // Azcapotzalco
            'کواوتموک', // Cuauhtémoc
            'مادرو', // Madero
            'سن پدرو گارسا گارسیا', // San Pedro Garza García
            'ایستکالکو', // Iztacalco
            'وینوستیانو کاران‌ثا', // Venustiano Carranza
            'آلوارو اوبرگون', // Álvaro Obregón
            'بنیتو خوآرز', // Benito Juárez
            'کویواکان', // Coyoacán
            'تسوک', // Tezuco
            'تولتیتلان', // Tultitlán
            'گوادالاجارا', // Guadalajara
            'ماتامورس', // Matamoros
            'خالپا', // Xalapa
            'انریکز', // Enriquez
            'واکساکا', // Oaxaca
            'تولان', // Tuxtla
            'ویلهرموسا', // Villahermosa
            'کوئرناواکا', // Cuernavaca
            'کامپچه', // Campeche
            'پاچوکا', // Pachuca
            'کولیما', // Colima
            'لاپاز', // La Paz
            'زاکاتکاس', // Zacatecas
            'تلاکسکالا', // Tlaxcala
            'چیلپانسینگو', // Chilpancingo
            'درانگو', // Durango
        ];
    }
}
