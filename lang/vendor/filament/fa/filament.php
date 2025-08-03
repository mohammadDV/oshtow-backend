<?php

return [
    'pages' => [
        'dashboard' => [
            'title' => 'داشبورد',
        ],
    ],
    'resources' => [
        'user' => [
            'label' => 'کاربر',
            'plural_label' => 'کاربران',
        ],
    ],
    'components' => [
        'actions' => [
            'modal' => [
                'heading' => 'تایید عملیات',
                'description' => 'آیا از انجام این عملیات اطمینان دارید؟',
                'actions' => [
                    'confirm' => [
                        'label' => 'تایید',
                    ],
                    'cancel' => [
                        'label' => 'انصراف',
                    ],
                ],
            ],
        ],
        'pagination' => [
            'label' => 'صفحه‌بندی',
            'overview' => [
                'text' => 'نمایش :first تا :last از :total نتیجه',
                'text_showing' => 'نمایش :first تا :last از :total نتیجه',
                'text_showing_total' => 'نمایش :first تا :last از :total نتیجه',
            ],
            'fields' => [
                'records_per_page' => [
                    'label' => 'تعداد در هر صفحه',
                    'options' => [
                        'all' => 'همه',
                    ],
                ],
            ],
            'actions' => [
                'first' => [
                    'label' => 'اولین',
                ],
                'go_to_page' => [
                    'label' => 'برو به صفحه :page',
                ],
                'last' => [
                    'label' => 'آخرین',
                ],
                'next' => [
                    'label' => 'بعدی',
                ],
                'previous' => [
                    'label' => 'قبلی',
                ],
            ],
        ],
        'tables' => [
            'empty' => [
                'heading' => 'هیچ رکوردی یافت نشد',
                'description' => 'هیچ رکوردی برای نمایش وجود ندارد.',
            ],
            'selection_indicator' => [
                'selected_count' => '1 رکورد انتخاب شده',
                'selected_count_text' => ':count رکورد انتخاب شده',
            ],
            'filters' => [
                'actions' => [
                    'remove_all' => [
                        'label' => 'حذف همه فیلترها',
                    ],
                ],
                'indicator' => [
                    'count' => '1 فیلتر اعمال شده',
                    'count_text' => ':count فیلتر اعمال شده',
                ],
            ],
            'reorder_indicator' => [
                'title' => 'مرتب‌سازی',
            ],
            'search' => [
                'placeholder' => 'جستجو...',
            ],
        ],
        'forms' => [
            'actions' => [
                'create' => [
                    'label' => 'ایجاد',
                ],
                'create_another' => [
                    'label' => 'ایجاد و ایجاد دیگری',
                ],
                'save' => [
                    'label' => 'ذخیره',
                ],
                'save_and_close' => [
                    'label' => 'ذخیره و بستن',
                ],
                'save_and_create_another' => [
                    'label' => 'ذخیره و ایجاد دیگری',
                ],
            ],
            'fields' => [
                'boolean' => [
                    'true' => 'بله',
                    'false' => 'خیر',
                ],
                'file_upload' => [
                    'actions' => [
                        'upload' => [
                            'label' => 'آپلود',
                        ],
                        'upload_new' => [
                            'label' => 'آپلود جدید',
                        ],
                    ],
                    'error' => [
                        'max_size' => 'حجم فایل نمی‌تواند بیشتر از :size باشد.',
                        'min_size' => 'حجم فایل نمی‌تواند کمتر از :size باشد.',
                    ],
                    'hint' => [
                        'max_size' => 'حداکثر حجم فایل: :size',
                        'min_size' => 'حداقل حجم فایل: :size',
                    ],
                ],
                'select' => [
                    'actions' => [
                        'create_option' => [
                            'modal' => [
                                'heading' => 'ایجاد گزینه جدید',
                                'description' => 'آیا می‌خواهید گزینه جدیدی ایجاد کنید؟',
                            ],
                        ],
                    ],
                    'boolean' => [
                        'true' => 'بله',
                        'false' => 'خیر',
                    ],
                    'placeholder' => 'انتخاب کنید...',
                ],
                'tags_input' => [
                    'placeholder' => 'تگ جدید...',
                ],
                'wizard' => [
                    'actions' => [
                        'previous_step' => [
                            'label' => 'قبلی',
                        ],
                        'next_step' => [
                            'label' => 'بعدی',
                        ],
                    ],
                ],
            ],
        ],
        'notifications' => [
            'actions' => [
                'close' => [
                    'label' => 'بستن',
                ],
                'mark_as_read' => [
                    'label' => 'علامت‌گذاری به عنوان خوانده شده',
                ],
            ],
        ],
        'modals' => [
            'actions' => [
                'close' => [
                    'label' => 'بستن',
                ],
            ],
        ],
    ],
    'pages' => [
        'auth' => [
            'login' => [
                'title' => 'ورود',
                'heading' => 'ورود به سیستم',
                'form' => [
                    'email' => [
                        'label' => 'ایمیل',
                    ],
                    'password' => [
                        'label' => 'رمز عبور',
                    ],
                    'remember' => [
                        'label' => 'مرا به خاطر بسپار',
                    ],
                    'actions' => [
                        'authenticate' => [
                            'label' => 'ورود',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
