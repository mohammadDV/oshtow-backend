<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateSingaporeProvincesAndCities extends Command
{
    protected $signature = 'populate:singapore-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Singapore provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Singapore provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Singapore country record');
            return 1;
        }

        $this->info("Using Singapore country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Singapore provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'سنگاپور')->first();

        if (!$country) {
            $this->info('Creating Singapore country record...');
            $country = Country::create([
                'title' => 'سنگاپور',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Singapore)...');

        $provinceName = 'سنگاپور';

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

        $province = Province::where('title', 'سنگاپور')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Singapore province not found. Please run --only-provinces first.');
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
            'سنگاپور', // Singapore (Central)
            'جورونگ وست', // Jurong West
            'بدوک', // Bedok
            'تامپینز', // Tampines
            'وودلندز', // Woodlands
            'سنگای گومباک', // Sengkang
            'یشون', // Yishun
            'چووا چو کانگ', // Choa Chu Kang
            'پنگنی جیا', // Punggol
            'هوگنگ', // Hougang
            'جورونگ ایست', // Jurong East
            'آنگ مو کیو', // Ang Mo Kio
            'بیشان', // Bishan
            'تووا پایوه', // Toa Payoh
            'پایا لبار', // Paya Lebar
            'کلمنتی', // Clementi
            'مارین پاراد', // Marina Parade
            'پاسیر ریس', // Pasir Ris
            'سیم لیم', // Sim Lim
            'کوین های', // Queen's Hill
            'بوکیت تیما', // Bukit Timah
            'نووتن', // Newton
            'ریور ولی', // River Valley
            'تنجونگ پاگر', // Tanjong Pagar
            'مارینا بای', // Marina Bay
            'چایناتاون', // Chinatown
            'لیتل ایندیا', // Little India
            'کامپونگ گلام', // Kampong Glam
            'اورچرد', // Orchard
            'کلارک کویی', // Clarke Quay
            'بوت کویی', // Boat Quay
            'سنتوسا', // Sentosa
            'چانگی', // Changi
            'سیرانگون', // Serangoon
            'ماک ریچی', // MacRitchie
            'بوکیت باتوک', // Bukit Batok
            'بوکیت پانجانگ', // Bukit Panjang
            'دوور', // Dover
            'کنت ریدج', // Kent Ridge
            'هاربورفرانت', // HarbourFront
            'آلکساندرا', // Alexandra
            'ریدهیل', // Redhill
            'کوینزتاون', // Queenstown
            'فارر پارک', // Farrer Park
            'گیلینگ', // Geylang
            'کیو', // Kew
            'مکفرسون', // Macpherson
            'کالانگ', // Kallang
            'داکوتا', // Dakota
            'کراچی', // Katong
            'مارین', // Marine
            'یو تونگ سن', // Eu Tong Sen
            'اولسترت', // Outram
            'تلوک آیر', // Telok Ayer
            'رافلز پلیس', // Raffles Place
            'سیتی هال', // City Hall
            'دھوبی گھاٹ', // Dhoby Ghaut
            'پروید', // Promenade
            'اسپلند', // Esplanade
            'بوگیس', // Bugis
            'لاوندر', // Lavender
            'جالان بسار', // Jalan Besar
            'بریداری رقل', // Bras Basah
            'بنکوک', // Bencoolen
            'روچور', // Rochor
        ];
    }
}
