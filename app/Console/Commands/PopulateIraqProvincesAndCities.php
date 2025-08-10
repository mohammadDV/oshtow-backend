<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateIraqProvincesAndCities extends Command
{
    protected $signature = 'populate:iraq-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Iraq provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Iraq provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Iraq country record');
            return 1;
        }

        $this->info("Using Iraq country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Iraq provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'عراق')->first();

        if (!$country) {
            $this->info('Creating Iraq country record...');
            $country = Country::create([
                'title' => 'عراق',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Iraq)...');

        $provinceName = 'عراق';

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

        $province = Province::where('title', 'عراق')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Iraq province not found. Please run --only-provinces first.');
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
            'بغداد', // Baghdad
            'البصره', // Basra
            'اربیل', // Erbil
            'الموصل', // Mosul
            'السلیمانیه', // Sulaymaniyah
            'النجف', // Najaf
            'کربلاء', // Karbala
            'کرکوک', // Kirkuk
            'الناصریه', // Nasiriyah
            'العماره', // Amarah
            'البعقوبه', // Baqubah
            'الحله', // Hillah
            'الرمادی', // Ramadi
            'الکوت', // Kut
            'سامراء', // Samarra
            'تکریت', // Tikrit
            'الفلوجه', // Fallujah
            'دهوک', // Dohuk
            'زاخو', // Zakho
            'عقره', // Aqrah
            'شیخان', // Sheikhan
            'سوران', // Soran
            'کلار', // Kalar
            'حلبجه', // Halabja
            'رانیه', // Rania
            'کویه', // Koya
            'دربندیخان', // Darbandikhan
            'پشدر', // Pshdar
            'میرگه سور', // Mergasur
            'شاربان', // Sharban
            'الحمدانیه', // Hamdaniya
            'قره قوش', // Qaraqosh
            'برطله', // Bartella
            'الشیخان', // Al-Shikhan
            'سنجار', // Sinjar
            'تلعفر', // Tal Afar
            'الحضر', // Al-Hadr
            'الشرقاط', // Shirqat
            'بیجی', // Baiji
            'الدور', // Al-Dawr
            'بلد', // Balad
            'الطوز', // Tuz Khurmatu
            'کفری', // Kafri
            'المقدادیه', // Muqdadiyah
            'خانقین', // Khanaqin
            'مندلی', // Mandali
            'بدره', // Badra
            'علی الغربی', // Ali al-Gharbi
            'العزیزیه', // Aziziyah
            'الصویره', // Suwayrah
            'المدائن', // Mada'in
            'ابو غریب', // Abu Ghraib
            'التاجی', // Taji
            'المحمودیه', // Mahmudiyah
            'الیوسفیه', // Yusufiyah
            'اللطیفیه', // Latifiyah
            'الاسکندریه', // Iskandariya
            'المسیب', // Musayyib
            'الهاشمیه', // Hashimiyah
            'عین تمر', // Ain Tamr
            'الرزازه', // Razzaza
            'طویریج', // Tuwayrij
            'الغراف', // Gharraf
            'الرفاعی', // Rifai
            'الچباش', // Chibayish
            'الجبایش', // Jubayish
            'الکحلاء', // Kahla
            'المیمونه', // Maymouna
            'البطحاء', // Batha
            'الفجر', // Fajr
            'الشامیه', // Shamiya
            'الهامشیه', // Hamishiya
            'القرنه', // Qurna
            'شط العرب', // Shatt al-Arab
            'الفاو', // Faw
            'صفوان', // Safwan
            'ام قصر', // Umm Qasr
            'الزبیر', // Zubayr
            'خور الزبیر', // Khor al-Zubayr
            'ابو الخصیب', // Abu al-Khasib
            'الطناف', // Tanf
            'راوه', // Rawah
            'عانه', // Anah
            'القائم', // Qaim
            'حدیثه', // Haditha
            'هیت', // Hit
            'الرطبه', // Rutba
            'النخیب', // Nukhayb
            'السماوه', // Samawah
            'الرمیثه', // Rumaitha
            'الخضر', // Khidr
            'الحمزه', // Hamza
            'ورکاء', // Warka
            'الشنافیه', // Shanafiya
            'البدیر', // Budayr
            'الفهود', // Fahud
            'المشرح', // Mishraq
            'الدیوانیه', // Diwaniyah
            'عفک', // Afak
            'الشامیه', // Shamiya (Qadisiyyah)
            'الحمزه الشرقی', // Hamza al-Sharqi
            'السنیه', // Saniya
            'غماس', // Ghammas
            'بابل', // Babylon
            'المحاویل', // Mahawil
            'الهاشمیه', // Hashimiya (Babylon)
            'القاسم', // Qasim
            'جرف الصخر', // Jurf al-Sakhar
            'الامام', // Imam
            'کیش', // Kish
            'عقرقوف', // Aqarquf
        ];
    }
}
