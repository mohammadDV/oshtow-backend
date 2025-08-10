<?php

namespace Core\Providers;

use Core\Console\Commands\AddPermissions;
use App\Console\Commands\PopulateIranProvincesAndCities;
use App\Console\Commands\PopulateTurkeyProvincesAndCities;
use App\Console\Commands\PopulateUAEProvincesAndCities;
use App\Console\Commands\PopulateIraqProvincesAndCities;
use App\Console\Commands\PopulateQatarProvincesAndCities;
use App\Console\Commands\PopulateArmeniaProvincesAndCities;
use App\Console\Commands\PopulateAfghanistanProvincesAndCities;
use App\Console\Commands\PopulateTurkmenistanProvincesAndCities;
use App\Console\Commands\PopulatePakistanProvincesAndCities;
use App\Console\Commands\PopulateAzerbaijanProvincesAndCities;
use App\Console\Commands\PopulateGeorgiaProvincesAndCities;
use App\Console\Commands\PopulateTajikistanProvincesAndCities;
// European Countries
use App\Console\Commands\PopulateGermanyProvincesAndCities;
use App\Console\Commands\PopulateFranceProvincesAndCities;
use App\Console\Commands\PopulateItalyProvincesAndCities;
use App\Console\Commands\PopulateSpainProvincesAndCities;
use App\Console\Commands\PopulateUKProvincesAndCities;
use App\Console\Commands\PopulateRussiaProvincesAndCities;
use App\Console\Commands\PopulatePolandProvincesAndCities;
use App\Console\Commands\PopulateNetherlandsProvincesAndCities;
use App\Console\Commands\PopulateBelgiumProvincesAndCities;
use App\Console\Commands\PopulateAustriaProvincesAndCities;
use App\Console\Commands\PopulateSwitzerlandProvincesAndCities;
use App\Console\Commands\PopulateGreeceProvincesAndCities;
use App\Console\Commands\PopulatePortugalProvincesAndCities;
use App\Console\Commands\PopulateSwedenProvincesAndCities;
use App\Console\Commands\PopulateNorwayProvincesAndCities;
use App\Console\Commands\PopulateDenmarkProvincesAndCities;
use App\Console\Commands\PopulateFinlandProvincesAndCities;
use App\Console\Commands\PopulateCzechRepublicProvincesAndCities;
use App\Console\Commands\PopulateUkraineProvincesAndCities;
use App\Console\Commands\PopulateBelarusProvincesAndCities;
// Asian Countries
use App\Console\Commands\PopulateChinaProvincesAndCities;
use App\Console\Commands\PopulateIndiaProvincesAndCities;
use App\Console\Commands\PopulateJapanProvincesAndCities;
use App\Console\Commands\PopulateSouthKoreaProvincesAndCities;
use App\Console\Commands\PopulateNorthKoreaProvincesAndCities;
use App\Console\Commands\PopulateIndonesiaProvincesAndCities;
use App\Console\Commands\PopulateThailandProvincesAndCities;
use App\Console\Commands\PopulatePhilippinesProvincesAndCities;
use App\Console\Commands\PopulateMalaysiaProvincesAndCities;
use App\Console\Commands\PopulateSingaporeProvincesAndCities;
// Oceania Countries
use App\Console\Commands\PopulateAustraliaProvincesAndCities;
use App\Console\Commands\PopulateNewZealandProvincesAndCities;
// Central Asian and Middle Eastern Countries
use App\Console\Commands\PopulateKyrgyzstanProvincesAndCities;
use App\Console\Commands\PopulateUzbekistanProvincesAndCities;
use App\Console\Commands\PopulateKazakhstanProvincesAndCities;
use App\Console\Commands\PopulateJordanProvincesAndCities;
use App\Console\Commands\PopulateLebanonProvincesAndCities;
use App\Console\Commands\PopulateOmanProvincesAndCities;
use App\Console\Commands\PopulateKuwaitProvincesAndCities;
use App\Console\Commands\PopulateBahrainProvincesAndCities;
use App\Console\Commands\PopulateSaudiArabiaProvincesAndCities;
// American Countries
use App\Console\Commands\PopulateCanadaProvincesAndCities;
use App\Console\Commands\PopulateUnitedStatesProvincesAndCities;
use App\Console\Commands\PopulateMexicoProvincesAndCities;
use App\Console\Commands\PopulateBrazilProvincesAndCities;
use App\Console\Commands\PopulateArgentinaProvincesAndCities;
// African Countries
use App\Console\Commands\PopulateAlgeriaProvincesAndCities;
use App\Console\Commands\PopulateEgyptProvincesAndCities;
use App\Console\Commands\PopulateNigeriaProvincesAndCities;
use App\Console\Commands\PopulateSouthAfricaProvincesAndCities;
use App\Console\Commands\PopulateMoroccoProvincesAndCities;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->commands([
            AddPermissions::class,
            PopulateIranProvincesAndCities::class,
            PopulateTurkeyProvincesAndCities::class,
            PopulateUAEProvincesAndCities::class,
            PopulateIraqProvincesAndCities::class,
            PopulateQatarProvincesAndCities::class,
            PopulateArmeniaProvincesAndCities::class,
            PopulateAfghanistanProvincesAndCities::class,
            PopulateTurkmenistanProvincesAndCities::class,
            PopulatePakistanProvincesAndCities::class,
            PopulateAzerbaijanProvincesAndCities::class,
            PopulateGeorgiaProvincesAndCities::class,
            PopulateTajikistanProvincesAndCities::class,
            // European Countries
            PopulateGermanyProvincesAndCities::class,
            PopulateFranceProvincesAndCities::class,
            PopulateItalyProvincesAndCities::class,
            PopulateSpainProvincesAndCities::class,
            PopulateUKProvincesAndCities::class,
            PopulateRussiaProvincesAndCities::class,
            PopulatePolandProvincesAndCities::class,
            PopulateNetherlandsProvincesAndCities::class,
            PopulateBelgiumProvincesAndCities::class,
            PopulateAustriaProvincesAndCities::class,
            PopulateSwitzerlandProvincesAndCities::class,
            PopulateGreeceProvincesAndCities::class,
            PopulatePortugalProvincesAndCities::class,
            PopulateSwedenProvincesAndCities::class,
            PopulateNorwayProvincesAndCities::class,
            PopulateDenmarkProvincesAndCities::class,
            PopulateFinlandProvincesAndCities::class,
            PopulateCzechRepublicProvincesAndCities::class,
            PopulateUkraineProvincesAndCities::class,
            PopulateBelarusProvincesAndCities::class,
            // Asian Countries
            PopulateChinaProvincesAndCities::class,
            PopulateIndiaProvincesAndCities::class,
            PopulateJapanProvincesAndCities::class,
            PopulateSouthKoreaProvincesAndCities::class,
            PopulateNorthKoreaProvincesAndCities::class,
            PopulateIndonesiaProvincesAndCities::class,
            PopulateThailandProvincesAndCities::class,
            PopulatePhilippinesProvincesAndCities::class,
            PopulateMalaysiaProvincesAndCities::class,
            PopulateSingaporeProvincesAndCities::class,
            // Oceania Countries
            PopulateAustraliaProvincesAndCities::class,
            PopulateNewZealandProvincesAndCities::class,
            // Central Asian and Middle Eastern Countries
            PopulateKyrgyzstanProvincesAndCities::class,
            PopulateUzbekistanProvincesAndCities::class,
            PopulateKazakhstanProvincesAndCities::class,
            PopulateJordanProvincesAndCities::class,
            PopulateLebanonProvincesAndCities::class,
            PopulateOmanProvincesAndCities::class,
            PopulateKuwaitProvincesAndCities::class,
            PopulateBahrainProvincesAndCities::class,
            PopulateSaudiArabiaProvincesAndCities::class,
            // American Countries
            PopulateCanadaProvincesAndCities::class,
            PopulateUnitedStatesProvincesAndCities::class,
            PopulateMexicoProvincesAndCities::class,
            PopulateBrazilProvincesAndCities::class,
            PopulateArgentinaProvincesAndCities::class,
            // African Countries
            PopulateAlgeriaProvincesAndCities::class,
            PopulateEgyptProvincesAndCities::class,
            PopulateNigeriaProvincesAndCities::class,
            PopulateSouthAfricaProvincesAndCities::class,
            PopulateMoroccoProvincesAndCities::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
