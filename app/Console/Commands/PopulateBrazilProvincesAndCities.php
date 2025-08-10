<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;

class PopulateBrazilProvincesAndCities extends Command
{
    protected $signature = 'populate:brazil-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    protected $description = 'Populate Brazil provinces and cities with proper relationships';

    public function handle()
    {
        $this->info('Starting Brazil provinces and cities population...');

        $country = $this->getOrCreateCountry();
        if (!$country) {
            $this->error('Failed to create Brazil country record');
            return 1;
        }

        $this->info("Using Brazil country (ID: {$country->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        if (!$onlyCities) {
            $this->populateProvinces($country, $force);
        }

        if (!$onlyProvinces) {
            $this->populateCities($country, $force);
        }

        $this->info('Brazil provinces and cities population completed successfully!');
        return 0;
    }

    private function getOrCreateCountry(): ?Country
    {
        $country = Country::where('title', 'برزیل')->first();

        if (!$country) {
            $this->info('Creating Brazil country record...');
            $country = Country::create([
                'title' => 'برزیل',
                'status' => 1
            ]);
        }

        return $country;
    }

    private function populateProvinces(Country $country, bool $force): void
    {
        $this->info('Populating province (Brazil)...');

        $provinceName = 'برزیل';

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

        $province = Province::where('title', 'برزیل')
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            $this->error('Brazil province not found. Please run --only-provinces first.');
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
            'سائوپائولو', // São Paulo
            'ریو د ژانیرو', // Rio de Janeiro
            'برازیلیا', // Brasília
            'سالوادور', // Salvador
            'فورتالزا', // Fortaleza
            'بلو هوریزونته', // Belo Horizonte
            'مانائوس', // Manaus
            'کوریتیبا', // Curitiba
            'رسیفه', // Recife
            'پورتو آلگره', // Porto Alegre
            'بلم', // Belém
            'گویانیا', // Goiânia
            'گوارولوش', // Guarulhos
            'کامپیناس', // Campinas
            'سائولویس', // São Luís
            'سائوگونسالو', // São Gonçalo
            'ماسیو', // Maceió
            'دوکه د کاکسیاس', // Duque de Caxias
            'کامپو گرانده', // Campo Grande
            'ناتال', // Natal
            'تراسینا', // Teresina
            'سائوبرناردو دو کامپو', // São Bernardo do Campo
            'نووا ایگواسو', // Nova Iguaçu
            'سائوژوائو د میریتی', // São João de Meriti
            'اوبرلاندیا', // Uberlândia
            'کونتاگم', // Contagem
            'آراکاژو', // Aracaju
            'فیرا د سانتانا', // Feira de Santana
            'کویابا', // Cuiabá
            'ژوینویل', // Joinville
            'ژوائو پسوا', // João Pessoa
            'ریبیرائو پرتو', // Ribeirão Preto
            'سوروکابا', // Sorocaba
            'جوندیائی', // Jundiaí
            'اولیندا', // Olinda
            'سانتوس', // Santos
            'موژی داس کروزس', // Mogi das Cruzes
            'مائورینگا', // Maringá
            'کاراپیکویبا', // Carapicuíba
            'پیراسیکابا', // Piracicaba
            'بائورو', // Bauru
            'کانواس', // Canoas
            'آنیاپولیس', // Anápolis
            'جوآز دو نورته', // Juazeiro do Norte
            'آپارسیدا د گویانیا', // Aparecida de Goiânia
            'بلفورد روکسو', // Belford Roxo
            'کولومبو', // Colombo
            'کوتیا', // Cotia
            'سائوژوزه دوس پینهایس', // São José dos Pinhais
            'پائولیستا', // Paulista
            'کارواکو', // Caruaru
            'فروپولیس', // Petrópolis
            'ولتا ردوندا', // Volta Redonda
            'ایپاتینگا', // Ipatinga
            'جویاس', // Goiás
            'فلوریانوپولیس', // Florianópolis
            'ویتوریا', // Vitória
            'بلاگی', // Blumenau
            'نیترو ی', // Niterói
            'کامپس دوس گوئیتاکازس', // Campos dos Goytacazes
            'سرا', // Serra
            'آگودوس', // Águas
            'سائوکایتانو دو سول', // São Caetano do Sul
            'پیتوپولیس', // Petrópolis
            'لاگو', // Lago
            'تبائوت', // Taboão da Serra
            'سائوویسنته', // São Vicente
            'سواره', // Sumaré
            'برگانتیم', // Bragantina
        ];
    }
}
