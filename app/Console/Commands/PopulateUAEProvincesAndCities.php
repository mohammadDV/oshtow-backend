<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateUAEProvincesAndCities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:uae-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate UAE provinces and cities with proper relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting UAE provinces and cities population...');

        // Get or create UAE country
        $uae = $this->getOrCreateUAECountry();

        if (!$uae) {
            $this->error('Failed to create UAE country record');
            return 1;
        }

        $this->info("Using UAE country (ID: {$uae->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        // Populate provinces
        if (!$onlyCities) {
            $this->populateProvinces($uae, $force);
        }

        // Populate cities
        if (!$onlyProvinces) {
            $this->populateCities($uae, $force);
        }

        $this->info('UAE provinces and cities population completed successfully!');
        return 0;
    }

    /**
     * Get or create UAE country
     */
    private function getOrCreateUAECountry(): ?Country
    {
        $uae = Country::where('title', 'امارات')->first();

        if (!$uae) {
            $uae = Country::where('title', 'UAE')->first();
        }

        if (!$uae) {
            $uae = Country::where('title', 'United Arab Emirates')->first();
        }

        if (!$uae) {
            $this->info('Creating UAE country record...');
            $uae = Country::create([
                'title' => 'امارات',
                'status' => 1
            ]);
        }

        return $uae;
    }

    /**
     * Populate provinces (only one province: UAE itself)
     */
    private function populateProvinces(Country $uae, bool $force): void
    {
        $this->info('Populating province (UAE)...');

        $provinceName = 'امارات'; // UAE province same as country

        $exists = Province::where('title', $provinceName)
            ->where('country_id', $uae->id)
            ->exists();

        if (!$exists || $force) {
            if ($force && $exists) {
                Province::where('title', $provinceName)
                    ->where('country_id', $uae->id)
                    ->update(['status' => 1]);
            } else {
                Province::create([
                    'title' => $provinceName,
                    'country_id' => $uae->id,
                    'status' => 1
                ]);
            }
        }

        $this->info('Province populated successfully!');
    }

    /**
     * Populate cities (all UAE cities under UAE province)
     */
    private function populateCities(Country $uae, bool $force): void
    {
        $this->info('Populating cities...');

        // Get the UAE province
        $province = Province::where('title', 'امارات')
            ->where('country_id', $uae->id)
            ->first();

        if (!$province) {
            $this->error('UAE province not found. Please run --only-provinces first.');
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

    /**
     * Get UAE cities data (flat array of all major UAE cities)
     */
    private function getCitiesData(): array
    {
        return [
            // Major cities across all emirates
            'دبی', // Dubai
            'ابوظبی', // Abu Dhabi
            'شارجه', // Sharjah
            'العین', // Al Ain
            'عجمان', // Ajman
            'رأس الخیمه', // Ras Al Khaimah
            'فجیره', // Fujairah
            'ام القیوین', // Umm Al Quwain
            'خورفکان', // Khor Fakkan
            'دبا الفجیره', // Dibba Al-Fujairah
            'کلباء', // Kalba
            'الرمس', // Al Rams
            'الضبعه', // Al Dhab'a
            'مدینه زاید', // Madinat Zayed
            'لیوا', // Liwa
            'الغربیه', // Al Gharbia
            'الرویس', // Ar Ruways
            'جبل علی', // Jebel Ali
            'دیره', // Deira
            'بر دبی', // Bur Dubai
            'جمیرا', // Jumeirah
            'مرینا دبی', // Dubai Marina
            'نخله جمیرا', // Palm Jumeirah
            'برج العرب', // Burj Al Arab Area
            'دبی لند', // Dubailand
            'دبی هیلز', // Dubai Hills
            'دبی ساوث', // Dubai South
            'مدینه دبی', // Dubai City
            'المرقبات', // Al Muraqabat
            'الکرامه', // Al Karama
            'ساتوا', // Satwa
            'ام هرار', // Umm Hurair
            'الوصل', // Al Wasl
            'الصفا', // Al Safa
            'ام سقیم', // Umm Suqeim
            'الباشا', // Al Barsha
            'موتور سیتی', // Motor City
            'اکادمیه دبی', // Dubai Academic City
            'دبی فستیوال سیتی', // Dubai Festival City
            'مدینه دبی للانترنت', // Dubai Internet City
            'مدینه دبی للاعلام', // Dubai Media City
            'قریه المعرفه', // Knowledge Village
            'الخلیج التجاری', // Business Bay
            'وسط المدینه', // Downtown Dubai
            'دبی مول', // Dubai Mall Area
            'برج خلیفه', // Burj Khalifa Area
            'الرحیل', // Al Raheel
            'المزهر', // Al Mizhar
            'القصیص', // Al Qusais
            'المهره', // Al Muhrah
            'الممزر', // Al Mamzar
            'الحمریه', // Al Hamriyah
            'الراشدیه', // Al Rashidiya
            'ند الشباء', // Nad Al Sheba
            'الخوانیج', // Al Khawaneej
            'ورقاء', // Warqaa
            'مردف', // Mirdif
            'الطوار', // Al Twar
            'الصدر', // Al Sadr
            'حتا', // Hatta
            'المناما', // Al Manama
            'الاتحاد', // Al Ittihad
            'البطین', // Al Bateen
            'الکورنیش', // Corniche
            'الکرامه ابوظبی', // Al Karama Abu Dhabi
            'الزعفرانه', // Al Zafarana
            'الخالدیه', // Al Khalidiya
            'الروضه', // Al Rowdah
            'الظاهر', // Al Dhahir
            'المرکزیه', // Al Markaziya
            'النهضه', // Al Nahda
            'الشویهان', // Al Shuwayhan
            'بنی یاس', // Bani Yas
            'مصفح', // Mussafah
            'المفرق', // Al Mafraq
            'الرحبه', // Al Rahba
            'الشامخه', // Al Shamkha
            'السعادات', // Al Saadiyat
            'یاس', // Yas Island
            'الریم', // Al Reem Island
            'مارینا الشارقه', // Sharjah Marina
            'الیرموک', // Al Yarmouk Sharjah
            'النود', // Al Nahda Sharjah
            'المجاز', // Al Majaz
            'الرولا', // Al Rawda Sharjah
            'الخان', // Al Khan
            'الحیره', // Al Heera
            'الطایه', // Al Tay
            'کورنیش عجمان', // Ajman Corniche
            'الروضه عجمان', // Al Rawda Ajman
            'الحمیدیه', // Al Humaidiya
            'الجرف', // Al Jurf
            'المویهات', // Al Mowaihat
            'الراقه', // Al Raqqa
            'النعیمیه', // Al Nuaimiya
            'کورنیش رأس الخیمه', // Ras Al Khaimah Corniche
            'النخیل', // Al Nakhil
            'الحمرا', // Al Hamra
            'جزیره المرجان', // Al Marjan Island
            'الجیر', // Al Jeer
            'المرفأ', // Al Marfa
            'الفحیحیل', // Al Fuhaihal
            'الطوای', // Al Tawei
            'فلج المعلا', // Falaj Al Mualla
            'دبا الحصن', // Dibba Al Hisn
            'مسافی', // Masafi
            'القدره', // Al Qidra
            'ینبع', // Yanbu (UAE)
        ];
    }
}
