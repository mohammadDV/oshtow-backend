<?php

namespace App\Console\Commands;

use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateIranProvincesAndCities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:iran-provinces-cities
                            {--force : Force update even if data exists}
                            {--only-provinces : Only populate provinces}
                            {--only-cities : Only populate cities}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate Iran provinces and cities with proper relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Iran provinces and cities population...');

        // Get or create Iran country
        $iran = $this->getOrCreateIranCountry();

        if (!$iran) {
            $this->error('Failed to create Iran country record');
            return 1;
        }

        $this->info("Using Iran country (ID: {$iran->id})");

        $onlyProvinces = $this->option('only-provinces');
        $onlyCities = $this->option('only-cities');
        $force = $this->option('force');

        // Populate provinces
        if (!$onlyCities) {
            $this->populateProvinces($iran, $force);
        }

        // Populate cities
        if (!$onlyProvinces) {
            $this->populateCities($iran, $force);
        }

        $this->info('Iran provinces and cities population completed successfully!');
        return 0;
    }

    /**
     * Get or create Iran country
     */
    private function getOrCreateIranCountry(): ?Country
    {
        $iran = Country::where('title', 'ایران')->first();


        if (!$iran) {
            $this->info('Creating Iran country record...');
            $iran = Country::create([
                'title' => 'ایران',
                'status' => 1
            ]);
        }

        return $iran;
    }

    /**
     * Populate provinces
     */
    private function populateProvinces(Country $iran, bool $force): void
    {
        $this->info('Populating provinces...');

        $provinces = $this->getProvincesData();

        $bar = $this->output->createProgressBar(count($provinces));
        $bar->start();

        foreach ($provinces as $provinceName) {
            $exists = Province::where('title', $provinceName)
                ->where('country_id', $iran->id)
                ->exists();

            if (!$exists || $force) {
                if ($force && $exists) {
                    Province::where('title', $provinceName)
                        ->where('country_id', $iran->id)
                        ->update(['status' => 1]);
                } else {
                    Province::create([
                        'title' => $provinceName,
                        'country_id' => $iran->id,
                        'status' => 1
                    ]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Provinces populated successfully!');
    }

    /**
     * Populate cities
     */
    private function populateCities(Country $iran, bool $force): void
    {
        $this->info('Populating cities...');

        $citiesData = $this->getCitiesData();
        $totalCities = 0;
        foreach ($citiesData as $cities) {
            $totalCities += count($cities);
        }

        $bar = $this->output->createProgressBar($totalCities);
        $bar->start();

        foreach ($citiesData as $provinceName => $cities) {
            $province = Province::where('title', $provinceName)
                ->where('country_id', $iran->id)
                ->first();

            if (!$province) {
                $this->warn("Province '{$provinceName}' not found. Skipping its cities.");
                $bar->advance(count($cities));
                continue;
            }

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
        }

        $bar->finish();
        $this->newLine();
        $this->info('Cities populated successfully!');
    }

    /**
     * Get provinces data
     */
    private function getProvincesData(): array
    {
        return [
            'آذربایجان شرقی',
            'آذربایجان غربی',
            'اردبیل',
            'اصفهان',
            'البرز',
            'ایلام',
            'بوشهر',
            'تهران',
            'چهارمحال و بختیاری',
            'خراسان جنوبی',
            'خراسان رضوی',
            'خراسان شمالی',
            'خوزستان',
            'زنجان',
            'سمنان',
            'سیستان و بلوچستان',
            'فارس',
            'قزوین',
            'قم',
            'کردستان',
            'کرمان',
            'کرمانشاه',
            'کهگیلویه و بویراحمد',
            'گلستان',
            'گیلان',
            'لرستان',
            'مازندران',
            'مرکزی',
            'هرمزگان',
            'همدان',
            'یزد'
        ];
    }

    /**
     * Get cities data organized by province
     */
    private function getCitiesData(): array
    {
        return [
            'تهران' => [
                'تهران', 'شهریار', 'ری', 'ورامین', 'رباط کریم', 'ملارد', 'قدس', 'اسلامشهر',
                'بهارستان', 'فیروزکوه', 'دماورند', 'پاکدشت', 'پیشوا'
            ],
            'اصفهان' => [
                'اصفهان', 'کاشان', 'خمینی‌شهر', 'نجف‌آباد', 'شاهین‌شهر', 'فولادشهر', 'زرین‌شهر',
                'مبارکه', 'خوانسار', 'گلپایگان', 'نطنز', 'اردستان', 'فریدن', 'فریدون‌شهر',
                'سمیرم', 'چادگان', 'بویین و میاندشت', 'تیران و کرون', 'برخوار', 'لنجان'
            ],
            'فارس' => [
                'شیراز', 'مرودشت', 'کازرون', 'جهرم', 'فسا', 'داراب', 'لارستان', 'آباده',
                'نی‌ریز', 'اقلید', 'لامرد', 'استهبان', 'فیروزآباد', 'سپیدان', 'پاسارگاد',
                'خرم‌بید', 'رستم', 'ممسنی', 'کوار', 'مهر', 'زرین‌دشت', 'بوانات', 'ارسنجان'
            ],
            'خراسان رضوی' => [
                'مشهد', 'نیشابور', 'سبزوار', 'قوچان', 'کاشمر', 'گناباد', 'تربت حیدریه',
                'تربت جام', 'خواف', 'طوس', 'چناران', 'کلات', 'درگز', 'فریمان', 'جوین',
                'بردسکن', 'رشتخوار', 'فیروزه', 'همت آباد'
            ],
            'خوزستان' => [
                'اهواز', 'آبادان', 'خرمشهر', 'دزفول', 'اندیمشک', 'شوشتر', 'بهبهان', 'مسجدسلیمان',
                'ایذه', 'شوش', 'رامهرمز', 'باغ‌ملک', 'هفتگل', 'شادگان', 'هندیجان', 'گتوند',
                'سوسنگرد', 'حمیدیه', 'کارون', 'لالی'
            ],
            'آذربایجان شرقی' => [
                'تبریز', 'مراغه', 'میانه', 'مرند', 'اهر', 'بناب', 'سراب', 'شبستر', 'عجب‌شیر',
                'آذرشهر', 'جلفا', 'ملکان', 'هشترود', 'ورزقان', 'کلیبر', 'خداآفرین', 'چاراویماق',
                'هریس'
            ],
            'آذربایجان غربی' => [
                'ارومیه', 'خوی', 'مهاباد', 'بوکان', 'میاندوآب', 'سلماس', 'نقده', 'سردشت',
                'پیرانشهر', 'تکاب', 'شاهین‌دژ', 'چالدران', 'ماکو', 'اشنویه', 'پلدشت', 'چایپاره'
            ],
            'کرمان' => [
                'کرمان', 'رفسنجان', 'زرند', 'جیرفت', 'بم', 'سیرجان', 'کهنوج', 'بردسیر',
                'رودبار جنوب', 'شهربابک', 'عنبرآباد', 'قلعه گنج', 'ریگان', 'فهرج', 'نرماشیر',
                'رابر', 'بافت', 'بلوک', 'منوجان'
            ],
            'گیلان' => [
                'رشت', 'بندر انزلی', 'لاهیجان', 'آستارا', 'رودسر', 'تالش', 'لنگرود', 'صومعه‌سرا',
                'فومن', 'ماسال', 'رودبار', 'شفت', 'آستانه اشرفیه', 'املش', 'سیاهکل', 'رضوانشهر'
            ],
            'مازندران' => [
                'ساری', 'بابل', 'آمل', 'قائم‌شهر', 'بابلسر', 'نوشهر', 'چالوس', 'تنکابن',
                'رامسر', 'بهشهر', 'نکا', 'فریدون‌کنار', 'جویبار', 'گلوگاه', 'محمودآباد',
                'سوادکوه', 'کلاردشت', 'عباس‌آباد', 'سوادکوه شمالی', 'میاندورود'
            ],
            'لرستان' => [
                'خرم‌آباد', 'بروجرد', 'دورود', 'الیگودرز', 'نورآباد', 'کوهدشت', 'ازنا',
                'پل‌دختر', 'دلفان', 'دوره', 'رومشکان', 'سلسله', 'چگنی'
            ],
            'کردستان' => [
                'سنندج', 'مریوان', 'بانه', 'سقز', 'کامیاران', 'قروه', 'بیجار', 'دیواندره',
                'دهگلان', 'سروآباد'
            ],
            'همدان' => [
                'همدان', 'ملایر', 'نهاوند', 'تویسرکان', 'اسدآباد', 'کبودراهنگ', 'رزن', 'بهار', 'فامنین'
            ],
            'یزد' => [
                'یزد', 'میبد', 'اردکان', 'بافق', 'ابرکوه', 'تفت', 'مهریز', 'اشکذر', 'خاتم', 'بهاباد'
            ],
            'هرمزگان' => [
                'بندر عباس', 'بندر لنگه', 'قشم', 'کیش', 'میناب', 'جاسک', 'پارسیان', 'رودان',
                'حاجی‌آباد', 'بستک', 'بشاگرد', 'سیریک', 'ابوموسی'
            ],
            'سیستان و بلوچستان' => [
                'زاهدان', 'چابهار', 'ایرانشهر', 'زابل', 'خاش', 'راسک', 'کنارک', 'سراوان',
                'نیک‌شهر', 'دلگان', 'هیرمند', 'هامون', 'فنوج', 'قصرقند', 'میرجاوه'
            ],
            'کرمانشاه' => [
                'کرمانشاه', 'اسلام‌آباد غرب', 'کنگاور', 'صحنه', 'قصر شیرین', 'هرسین', 'سنقر',
                'جوانرود', 'گیلان غرب', 'سرپل ذهاب', 'روانسر', 'ثلاث باباجانی', 'دالاهو', 'پاوه'
            ],
            'بوشهر' => [
                'بوشهر', 'برازجان', 'خارک', 'گناوه', 'دیر', 'کنگان', 'جم', 'عسلویه', 'دیلم', 'دشتی'
            ],
            'زنجان' => [
                'زنجان', 'ابهر', 'خرمدره', 'قیدار', 'ایجرود', 'ماهنشان', 'سلطانیه', 'طارم'
            ],
            'سمنان' => [
                'سمنان', 'شاهرود', 'گرمسار', 'دامغان', 'مهدی‌شهر', 'میامی', 'سرخه', 'آرادان'
            ],
            'قم' => [
                'قم'
            ],
            'قزوین' => [
                'قزوین', 'البرز', 'بوئین‌زهرا', 'تاکستان', 'آوج'
            ],
            'گلستان' => [
                'گرگان', 'گنبد کاووس', 'علی‌آباد کتول', 'آق‌قلا', 'بندر ترکمن', 'کردکوی',
                'مینودشت', 'آزادشهر', 'رامیان', 'کلاله', 'مراوه‌تپه', 'گالیکش', 'بندرگز'
            ],
            'خراسان شمالی' => [
                'بجنورد', 'اسفراین', 'شیروان', 'فاروج', 'جاجرم', 'مانه و سملقان', 'راز و جرگلان'
            ],
            'خراسان جنوبی' => [
                'بیرجند', 'قائن', 'فردوس', 'طبس', 'نهبندان', 'سرایان', 'درمیان', 'زیرکوه',
                'بشرویه', 'خوسف', 'سربیشه'
            ],
            'اردبیل' => [
                'اردبیل', 'پارس‌آباد', 'خلخال', 'مشگین‌شهر', 'گرمی', 'نمین', 'نیر', 'سرعین',
                'کوثر', 'بیله‌سوار'
            ],
            'البرز' => [
                'کرج', 'فردیس', 'نظرآباد', 'طالقان', 'ساوجبلاغ', 'اشتهارد', 'هشتگرد', 'چهارباغ'
            ],
            'ایلام' => [
                'ایلام', 'دهلران', 'آبدانان', 'مهران', 'ایوان', 'دره‌شهر', 'شیروان و چرداول',
                'ملکشاهی', 'سیروان', 'بدره'
            ],
            'چهارمحال و بختیاری' => [
                'شهرکرد', 'بروجن', 'فارسان', 'لردگان', 'اردل', 'کوهرنگ', 'بن', 'سامان', 'کیار'
            ],
            'کهگیلویه و بویراحمد' => [
                'یاسوج', 'گچساران', 'دوگنبدان', 'دهدشت', 'لیکک', 'چرام', 'بویراحمد', 'بهمئی'
            ],
            'مرکزی' => [
                'اراک', 'ساوه', 'خمین', 'محلات', 'دلیجان', 'تفرش', 'آشتیان', 'کمیجان', 'شازند', 'فراهان'
            ]
        ];
    }
}
