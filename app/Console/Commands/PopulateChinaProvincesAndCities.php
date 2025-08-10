<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateChinaProvincesAndCities extends Command
{
    protected $signature = 'populate:china-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate China provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting China provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create China country record');
            return 1;
        }

        $this->info("Using China country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('China provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'چین')->first();

        if (!$country) {
            $this->info('Creating China country record...');
            $country = Country::create([
                'title' => 'چین',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (China)...');

        $provinceName = 'چین';

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

        $province = Province::where('title', 'چین')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('China province not found. Please run --only-provinces first.');
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
            'پکن', // Beijing
            'شانگهای', // Shanghai
            'گوانگژو', // Guangzhou
            'شنژن', // Shenzhen
            'تیانجین', // Tianjin
            'ووهان', // Wuhan
            'دونگگوان', // Dongguan
            'چنگدو', // Chengdu
            'نانجینگ', // Nanjing
            'چونگ‌کینگ', // Chongqing
            'شیان', // Xi'an
            'سوژو', // Suzhou
            'هانگژو', // Hangzhou
            'فوشان', // Foshan
            'شن‌یانگ', // Shenyang
            'هاربین', // Harbin
            'تشنگژائو', // Zhengzhou
            'کوانمینگ', // Kunming
            'چانگشا', // Changsha
            'تایوان', // Taiyuan
            'شیجیاژوانگ', // Shijiazhuang
            'اورومچی', // Urumqi
            'جینان', // Jinan
            'دالیان', // Dalian
            'چانگچون', // Changchun
            'نانینگ', // Nanning
            'گویانگ', // Guiyang
            'نانچانگ', // Nanchang
            'فوژو', // Fuzhou
            'ووکسی', // Wuxi
            'ژوهای', // Zhuhai
            'شانتو', // Shantou
            'ویفانگ', // Weifang
            'ژیبو', // Zibo
            'یانتای', // Yantai
            'تایژو', // Taizhou
            'خفی', // Hefei
            'هویژو', // Huizhou
            'جیانگمن', // Jiangmen
            'لانژو', // Lanzhou
            'هاکو', // Haikou
            'نینگبو', // Ningbo
            'شائوکسینگ', // Shaoxing
            'ونژو', // Wenzhou
            'ژونگشان', // Zhongshan
            'ژاوژینگ', // Zhaoqing
            'یینچوان', // Yinchuan
            'ژانگژیا کو', // Zhangjiakou
            'قینگدائو', // Qingdao
            'بائودینگ', // Baoding
            'تانگشان', // Tangshan
            'هاندان', // Handan
            'داتونگ', // Datong
            'لویانگ', // Luoyang
            'کایفنگ', // Kaifeng
            'آنیانگ', // Anyang
            'ژانگژو', // Zhangzhou
            'کوامی', // Quanzhou
            'پوتیان', // Putian
            'لونگیان', // Longyan
            'نانپینگ', // Nanping
            'ژودیان', // Zhumadian
            'زینیانگ', // Xinyang
            'نانیانگ', // Nanyang
            'لوهه', // Luohe
            'جیاوزو', // Jiaozuo
            'پویانگ', // Puyang
            'شانکیو', // Shangqiu
            'کایلی', // Kaili
            'انشون', // Anshun
            'توانریان', // Tongren
            'لیوپانگ شوی', // Liupanshui
            'ژونی', // Zunyi
        ];
    }
}
