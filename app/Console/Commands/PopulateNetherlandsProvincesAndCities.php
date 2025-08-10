<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateNetherlandsProvincesAndCities extends Command
{
    protected $signature = 'populate:netherlands-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Netherlands provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Netherlands provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Netherlands country record');
            return 1;
        }

        $this->info("Using Netherlands country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Netherlands provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'هلند')->first();

        if (!$country) {
            $this->info('Creating Netherlands country record...');
            $country = Country::create([
                'title' => 'هلند',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Netherlands)...');

        $provinceName = 'هلند';

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

        $province = Province::where('title', 'هلند')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Netherlands province not found. Please run --only-provinces first.');
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
            'آمستردام', // Amsterdam
            'روتردام', // Rotterdam
            'لاهه', // The Hague
            'اوترخت', // Utrecht
            'آیندهوون', // Eindhoven
            'تیلبورگ', // Tilburg
            'گرونینگن', // Groningen
            'آلمره', // Almere
            'بردا', // Breda
            'نایمخن', // Nijmegen
            'انسخده', // Enschede
            'هارلم', // Haarlem
            'آرنم', // Arnhem
            'زاندام', // Zaandam
            'های‌زن‌هاین', // Haarlemmermeer
            'آمرسفورت', // Amersfoort
            'آپلدورن', // Apeldoorn
            'زوترمیر', // Zoetermeer
            'زوله', // Zwolle
            'دن بوش', // 's-Hertogenbosch
            'ماستریخت', // Maastricht
            'لیدن', // Leiden
            'دلفت', // Delft
            'دوردرخت', // Dordrecht
            'آلکمار', // Alkmaar
            'لیوردن', // Leeuwarden
            'ایدی', // Ede
            'هنگلو', // Hengelo
            'عاصمت', // Alphen aan den Rijn
            'هوفدورپ', // Hoofddorp
            'امن', // Emmen
            'رویسندال', // Roosendaal
            'هلموند', // Helmond
            'دیون آن بال', // Vlaardingen
            'پورمرند', // Purmerend
            'شیدام', // Schiedam
            'اوسترزیل', // Oosterhout
            'کامپن', // Kampen
            'کاپله آن دن آیسل', // Capelle aan den IJssel
            'ون دی ورک', // Venlo
            'دیون آن بال', // Vlaardingen
            'اوده راین', // Oude Rijn
            'هرن', // Heerlen
            'آسن', // Assen
            'سپنخل‌جن', // Spijkenisse
            'گدا', // Gouda
            'مایدورپ', // Middelburg
            'هیلورسوم', // Hilversum
        ];
    }
}