<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateRussiaProvincesAndCities extends Command
{
    protected $signature = 'populate:russia-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Russia provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Russia provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Russia country record');
            return 1;
        }

        $this->info("Using Russia country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Russia provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'روسیه')->first();

        if (!$country) {
            $this->info('Creating Russia country record...');
            $country = Country::create([
                'title' => 'روسیه',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Russia)...');

        $provinceName = 'روسیه';

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

        $province = Province::where('title', 'روسیه')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Russia province not found. Please run --only-provinces first.');
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
            'مسکو', // Moscow
            'سن پترزبورگ', // St. Petersburg
            'نووسیبیرسک', // Novosibirsk
            'یکاترینبورگ', // Yekaterinburg
            'نیژنی نووگورود', // Nizhny Novgorod
            'کازان', // Kazan
            'چلیابینسک', // Chelyabinsk
            'اومسک', // Omsk
            'ساماره', // Samara
            'روستوف نا دونو', // Rostov-on-Don
            'اوفا', // Ufa
            'کراسنویارسک', // Krasnoyarsk
            'ورونژ', // Voronezh
            'پرم', // Perm
            'ولگوگراد', // Volgograd
            'کراسنودار', // Krasnodar
            'ساراتوف', // Saratov
            'تیومن', // Tyumen
            'تولیاتی', // Tolyatti
            'ایژوسک', // Izhevsk
            'بارناوئل', // Barnaul
            'اولیانوسک', // Ulyanovsk
            'ایرکوتسک', // Irkutsk
            'خاباروفسک', // Khabarovsk
            'یاروسلاول', // Yaroslavl
            'ولادیووستوک', // Vladivostok
            'ماخاچکالا', // Makhachkala
            'تومسک', // Tomsk
            'اورنبورگ', // Orenburg
            'کمرووو', // Kemerovo
            'آستراخان', // Astrakhan
            'ریازان', // Ryazan
            'نابرژنی چلنی', // Naberezhnye Chelny
            'پنزا', // Penza
            'لیپتسک', // Lipetsk
            'تولا', // Tula
            'کیروف', // Kirov
            'چبوکساری', // Cheboksary
            'بلگورود', // Belgorod
            'کالینینگراد', // Kaliningrad
            'کورسک', // Kursk
            'اسماعیل', // Smolensk
            'برانسک', // Bryansk
            'ایوانووو', // Ivanovo
            'مگنیتوگورسک', // Magnitogorsk
            'تویر', // Tver
            'ستاورپول', // Stavropol
            'نیژنی تاگیل', // Nizhny Tagil
            'آرخانگلسک', // Arkhangelsk
            'کالوگا', // Kaluga
            'سوچی', // Sochi
            'ولیکی نووگورود', // Veliky Novgorod
            'چریپووتس', // Cherepovets
            'یوشکار اولا', // Yoshkar-Ola
            'کوستروما', // Kostroma
            'نووگورود', // Novgorod
            'تامبوف', // Tambov
            'کوموسومولسک نا آموره', // Komsomolsk-on-Amur
            'پترزاودسک', // Petrozavodsk
            'نووی اورنگوی', // Novy Urengoy
            'تاگانروگ', // Taganrog
            'یورگا', // Yurga
            'سرگییو پوساد', // Sergiyev Posad
            'سیختیفکار', // Syktyvkar
            'نالچیک', // Nalchik
            'شختی', // Shakhty
            'دزرژینسک', // Dzerzhinsk
            'ارلان', // Orlan
            'نیژنوارتوسک', // Nizhnevartovsk
            'مورمانسک', // Murmansk
            'تامبو', // Tambov
            'یاکوتسک', // Yakutsk
        ];
    }
}
