<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateAfghanistanProvincesAndCities extends Command
{
    protected $signature = 'populate:afghanistan-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Afghanistan provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Afghanistan provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Afghanistan country record');
            return 1;
        }

        $this->info("Using Afghanistan country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Afghanistan provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'افغانستان')->first();

        if (!$country) {
            $this->info('Creating Afghanistan country record...');
            $country = Country::create([
                'title' => 'افغانستان',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Afghanistan)...');

        $provinceName = 'افغانستان';

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

        $province = Province::where('title', 'افغانستان')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Afghanistan province not found. Please run --only-provinces first.');
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
            'کابل', // Kabul
            'هرات', // Herat
            'قندهار', // Kandahar
            'مزار شریف', // Mazar-i-Sharif
            'جلال آباد', // Jalalabad
            'کندز', // Kunduz
            'لشکرگاه', // Lashkar Gah
            'تالقان', // Taloqan
            'پل خمری', // Pol-e Khomri
            'غزنی', // Ghazni
            'قلعه نو', // Qala-e-Naw
            'چغچران', // Chaghcharan
            'میمنه', // Maimana
            'بامیان', // Bamyan
            'گردیز', // Gardez
            'خوست', // Khost
            'فیض آباد', // Fayzabad
            'آیبک', // Aybak
            'شیبرغان', // Sheberghan
            'قلات', // Qalat
            'ترین کوت', // Tarin Kot
            'زرنج', // Zaranj
            'فراه', // Farah
            'چاریکار', // Charikar
            'میدان شهر', // Maidan Shahr
            'نیلی', // Nili
            'پنجشیر', // Panjshir
            'شرنا', // Sharan
            'سرپل', // Sar-e Pol
            'شینداند', // Shindand
            'کوهستان', // Kohistan
            'بغلان', // Baghlan
            'اندخوی', // Andkhoy
            'دشت قلعه', // Dasht-e Qala
            'دولت آباد', // Dawlat Abad
            'خلم', // Kholm
            'نهرین', // Nahrin
            'پلک', // Pulk
            'درای صوف', // Dara-e Suf
            'سمنگان', // Samangan
            'خنجان', // Khanjan
            'بدخشان', // Badakhshan
            'واخان', // Wakhan
            'اشکاشم', // Ishkashim
            'روستاق', // Rustaq
            'خواجه گار', // Khwaja Ghar
            'قرقین', // Qarqin
            'اقچه', // Aqcha
            'خان آباد', // Khan Abad
            'ارچی', // Archi
            'امام صاحب', // Imam Sahib
            'دشت ارچی', // Dasht-e Archi
            'قل', // Qal
            'علی آباد', // Ali Abad
            'چهارباغ', // Charbagh
            'کلاب', // Kolab
            'شوراب', // Shurab
            'چاردره', // Char Dara
            'بانگی', // Bangi
            'ناوه', // Nawa
            'کهمرد', // Kahmard
            'واراس', // Waras
            'پیتو', // Pite
            'ینگی قلعه', // Yangi Qala
            'شورتپه', // Shurtepa
            'گوریان', // Ghorian
            'کرخ', // Karukh
            'کوهسان', // Kohsan
            'ادرسکن', // Adraskan
            'پشتون زرغون', // Pashtun Zarghun
            'انجیل', // Injil
            'گلران', // Golran
            'چشت شریف', // Chesht-e Sharif
            'ارغنداب', // Arghandab
            'کهندز', // Dand
            'شیون', // Khon
            'پنجوای', // Panjwai
            'میوند', // Maywand
            'ژری', // Zhari
            'رگ', // Reg
            'نیش', // Nish
            'شومالی', // Shomali
            'تخار', // Takhar
            'وردج', // Warduj
            'راغ', // Ragh
            'ینگی قلعه', // Yangi Qala (Takhar)
            'چاه آب', // Chah Ab
            'بانگی', // Bangi (Takhar)
            'فرخار', // Farkhar
            'کلفگان', // Kalafgan
            'ورسج', // Warsaj
            'خواجه بهاو الدین', // Khwaja Bahauddin
            'یتیم تپه', // Yateem Tapa
            'هزارسموچ', // Hazarsumouch
            'بهرک', // Baharak
            'جرم', // Jurm
            'تگاب', // Tagab
            'شهری نو', // Shahr-e Naw
            'کوهی استاد', // Koh-e Ostad
        ];
    }
}
