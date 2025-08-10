<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateTurkeyProvincesAndCities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:turkey-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate Turkey provinces and cities with proper relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Turkey provinces and cities population...');

        // Get or create Turkey country
        $turkey = $this->getOrCreateTurkeyCountry();

        if (!$turkey) {
            $this->error('Failed to create Turkey country record');
            return 1;
        }

        $this->info("Using Turkey country (ID: {$turkey->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        // Populate provinces
        if (!$onlyCities) {
            $this->populateProvinces($turkey, $force);
        }

        // Populate cities
        if (!$onlyProvinces) {
            $this->populateCities($turkey, $force);
        }

        $this->info('Turkey provinces and cities population completed successfully!');
        return 0;
    }

    /**
     * Get or create Turkey country
     */
    private function getOrCreateTurkeyCountry(): ?Country
    {
        $turkey = Country::where('title', 'ترکیه')->first();

        if (!$turkey) {
            $turkey = Country::where('title', 'Turkey')->first();
        }

        if (!$turkey) {
            $this->info('Creating Turkey country record...');
            $turkey = Country::create([
                'title' => 'ترکیه',
                'status' => 1
            ]);
        }

        return $turkey;
    }

    /**
     * Populate provinces (only one province: Turkey itself)
     */
    private function populateProvinces(Country $turkey, bool $force): void
    {
        $this->info('Populating province (Turkey)...');

        $provinceName = 'ترکیه'; // Turkey province same as country

        $exists = Province::where('title', $provinceName)
            ->where('country_id', $turkey->id)
            ->exists();

        if (!$exists || $force) {
            if ($force && $exists) {
                Province::where('title', $provinceName)
                    ->where('country_id', $turkey->id)
                    ->update(['status' => 1]);
            } else {
                Province::create([
                    'title' => $provinceName,
                    'country_id' => $turkey->id,
                    'status' => 1
                ]);
            }
        }

        $this->info('Province populated successfully!');
    }

    /**
     * Populate cities (all Turkish cities under Turkey province)
     */
    private function populateCities(Country $turkey, bool $force): void
    {
        $this->info('Populating cities...');

        // Get the Turkey province
        $province = Province::where('title', 'ترکیه')
            ->where('country_id', $turkey->id)
            ->first();

        if (!$province) {
            $this->error('Turkey province not found. Please run --only-provinces first.');
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
     * Get Turkish cities data (flat array of all major Turkish cities)
     */
    private function getCitiesData(): array
    {
        return [
            'استانبول', // Istanbul
            'آنکارا', // Ankara
            'ازمیر', // İzmir
            'بورسا', // Bursa
            'آدانا', // Adana
            'گازی‌عینتاب', // Gaziantep
            'کونیا', // Konya
            'آنتالیا', // Antalya
            'کایسری', // Kayseri
            'مرسین', // Mersin
            'دیاربکر', // Diyarbakır
            'انتاکیا', // Antakya
            'سامسون', // Samsun
            'کاهرمان‌مرعش', // Kahramanmaraş
            'ارزروم', // Erzurum
            'وان', // Van
            'باتمان', // Batman
            'الازیغ', // Elazığ
            'ارزینجان', // Erzincan
            'تکیرداغ', // Tekirdağ
            'بالیکسیر', // Balıkesir
            'ترابزون', // Trabzon
            'کجه‌الی', // Kocaeli
            'ماردین', // Mardin
            'مانیسا', // Manisa
            'اسکی‌شهیر', // Eskişehir
            'ملاطیه', // Malatya
            'اردو', // Ordu
            'دنیزلی', // Denizli
            'سیواس', // Sivas
            'ساکاریا', // Sakarya
            'زونگولداک', // Zonguldak
            'شانلی‌اورفا', // Şanlıurfa
            'کارس', // Kars
            'آفیون', // Afyonkarahisar
            'اوشاک', // Uşak
            'کوتاهیا', // Kütahya
            'موغلا', // Muğla
            'توکات', // Tokat
            'آیدین', // Aydın
            'چانکیری', // Çankırı
            'چوروم', // Çorum
            'کاستامونو', // Kastamonu
            'بولو', // Bolu
            'سینوپ', // Sinop
            'ریزه', // Rize
            'چاناک‌کاله', // Çanakkale
            'یوزگات', // Yozgat
            'کیرشهیر', // Kırşehir
            'نوشهیر', // Nevşehir
            'نیغده', // Niğde
            'آکسارای', // Aksaray
            'کارامان', // Karaman
            'اسپارتا', // Isparta
            'بوردور', // Burdur
            'آنطالیا', // Antalya (Alternative spelling)
            'کیریکاله', // Kırıkkale
            'کیرکلاره‌لی', // Kırklareli
            'ادیرنه', // Edirne
            'بیلجیک', // Bilecik
            'دوزجه', // Düzce
            'یالووا', // Yalova
            'کاراباوک', // Karabük
            'بارتین', // Bartın
            'گومش‌خانه', // Gümüşhane
            'آرتوین', // Artvin
            'بایبورت', // Bayburt
            'اردهان', // Ardahan
            'ایغدیر', // Iğdır
            'موش', // Muş
            'بینگول', // Bingöl
            'بیتلیس', // Bitlis
            'تونجلی', // Tunceli
            'هاکاری', // Hakkâri
            'شیرناک', // Şırnak
            'سی‌ایرت', // Siirt
            'آغری', // Ağrı
            'آماسیا', // Amasya
            'آدیامان', // Adıyaman
            'کیلیس', // Kilis
            'عثمانیه', // Osmaniye
            'گیرسون', // Giresun
            'اسمیرنا', // Smyrna (historical name for İzmir)
            'برصا', // Alternative for Bursa
            'قسطنطنیه' // Constantinople (historical name for Istanbul)
        ];
    }
}
