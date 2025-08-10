<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateArgentinaProvincesAndCities extends Command
{
    protected $signature = 'populate:argentina-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Argentina provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Argentina provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Argentina country record');
            return 1;
        }

        $this->info("Using Argentina country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Argentina provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'آرژانتین')->first();

        if (!$country) {
            $this->info('Creating Argentina country record...');
            $country = Country::create([
                'title' => 'آرژانتین',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Argentina)...');

        $provinceName = 'آرژانتین';

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

        $province = Province::where('title', 'آرژانتین')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Argentina province not found. Please run --only-provinces first.');
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
            'بوئنوس آیرس', // Buenos Aires
            'کوردوبا', // Córdoba
            'روساریو', // Rosario
            'مندوزا', // Mendoza
            'توکومان', // Tucumán
            'مار دل پلاتا', // Mar del Plata
            'سالتا', // Salta
            'سانتا فه', // Santa Fe
            'سان خوان', // San Juan
            'نیوکن', // Neuquén
            'فورموسا', // Formosa
            'کوریینتس', // Corrientes
            'پوساداس', // Posadas
            'کیلمس', // Quilmes
            'سان میگل د توکومان', // San Miguel de Tucumán
            'بانفیلد', // Banfield
            'لانوس', // Lanús
            'مورون', // Morón
            'کازتلار', // Castelar
            'لوماس د زامورا', // Lomas de Zamora
            'آلمیرانته براون', // Almirante Brown
            'مرلو', // Merlo
            'سان ایسیدرو', // San Isidro
            'اسکوبار', // Escobar
            'تیگره', // Tigre
            'ملازنز', // Malvinas
            'اسکوبار', // Escobar
            'فلورنسیو ورلا', // Florencio Varela
            'بکار', // Becar
            'تاندیل', // Tandil
            'بهیا بلانکا', // Bahía Blanca
            'ریو کوارتو', // Río Cuarto
            'سان کارلوس د بریلوچه', // San Carlos de Bariloche
            'یوساهیا', // Ushuaia
            'ریو گاگوس', // Río Gallegos
            'چوبوت', // Comodoro Rivadavia
            'سان سلوادور د خوجوی', // San Salvador de Jujuy
            'سانتیاگو دل استرو', // Santiago del Estero
            'لا ریاجا', // La Rioja
            'کاتمارکا', // Catamarca
            'پارانا', // Paraná
            'کونکورده', // Concordia
            'ریو تسیرو', // Río Tercero
            'ویلا مرسدس', // Villa Mercedes
            'سان رافائل', // San Rafael
            'خنرال پیکو', // General Pico
            'ویلا ماریا', // Villa María
            'راونا', // Rawson
            'ترلو', // Trelew
            'سان نیکولاس', // San Nicolás
            'پرگامینو', // Pergamino
            'چیویلکوی', // Chivilcoy
            'سان پدرو', // San Pedro
            'اولاوریا', // Olavarría
            'جونین', // Junín
            'آزول', // Azul
            'تس آرویوس', // Tres Arroyos
            'نکوچا', // Necochea
            'دولورس', // Dolores
            'چاسکوموس', // Chascomús
            'سان آنتونیو د آرکو', // San Antonio de Areco
            'مرسدس', // Mercedes
            'سوئیپاچا', // Suipacha
            'سالادیلو', // Saladillo
            'ریوسیکو', // Riachuelo
            'مونته گرانده', // Monte Grande
            'ادوآردو کاستکس', // Eduardo Castex
        ];
    }
}
