<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IdentityRecordResource\Pages;
use Carbon\Carbon;
use Domain\IdentityRecord\Models\IdentityRecord;
use Domain\Notification\Services\NotificationService;
use Domain\Plan\Models\Plan;
use Domain\Plan\Repositories\SubscribeRepository;
use Domain\User\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Morilog\Jalali\Jalalian;
use Filament\Notifications\Notification;

class IdentityRecordResource extends Resource
{
    protected static ?string $model = IdentityRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('site.identity_records');
    }

    public static function getModelLabel(): string
    {
        return __('site.identity_record');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.identity_records');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.Identity Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('site.personal_information'))
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label(__('site.first_name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label(__('site.last_name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('national_code')
                            ->label(__('site.national_code'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('mobile')
                            ->label(__('site.mobile'))
                            ->required()
                            ->maxLength(15),
                        Forms\Components\DatePicker::make('birthday')
                            ->label(__('site.birthday'))
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label(__('site.email'))
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make(__('site.address_information'))
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->label(__('site.country'))
                            ->options(fn () => static::getCountryOptions())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('province_id', null)),
                        Forms\Components\Select::make('province_id')
                            ->label(__('site.province'))
                            ->options(fn (callable $get) =>
                                $get('country_id')
                                    ? \Domain\Address\Models\Province::where('country_id', $get('country_id'))
                                        ->select('id', 'title')
                                        ->pluck('title', 'id')
                                        ->toArray()
                                    : static::getProvinceOptions()
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('city_id', null)),
                        Forms\Components\Select::make('city_id')
                            ->label(__('site.city'))
                            ->options(fn (callable $get) =>
                                $get('province_id')
                                    ? \Domain\Address\Models\City::where('province_id', $get('province_id'))
                                        ->select('id', 'title')
                                        ->pluck('title', 'id')
                                        ->toArray()
                                    : static::getCityOptions()
                            )
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('postal_code')
                            ->label(__('site.postal_code'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->label(__('site.address'))
                            ->required()
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make(__('site.documents'))
                    ->schema([
                        Forms\Components\FileUpload::make('image_national_code_front')
                            ->label(__('site.national_code_front'))
                            ->placeholder(__('site.upload_national_code_front'))
                            ->image()
                            ->imageEditor()
                            ->required()
                            ->disk('s3')
                            ->directory('/identity-records/national-code-front'),
                        Forms\Components\FileUpload::make('image_national_code_back')
                            ->label(__('site.national_code_back'))
                            ->placeholder(__('site.upload_national_code_back'))
                            ->image()
                            ->imageEditor()
                            ->required()
                            ->disk('s3')
                            ->directory('/identity-records/national-code-back'),
                        Forms\Components\FileUpload::make('video')
                            ->label(__('site.video'))
                            ->placeholder(__('site.upload_video'))
                            ->acceptedFileTypes(['video/*'])
                            ->required()
                            ->disk('s3')
                            ->directory('/identity-records/videos'),
                    ])->columns(3),

                Forms\Components\Section::make(__('site.status_information'))
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label(__('site.status'))
                            ->options([
                                IdentityRecord::PENDING => __('site.pending'),
                                IdentityRecord::PAID => __('site.paid'),
                                IdentityRecord::COMPLETED => __('site.completed'),
                                IdentityRecord::REJECT => __('site.reject'),
                                IdentityRecord::INPROGRESS => __('site.in_progress'),
                            ])
                            ->default(IdentityRecord::PENDING)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->deferLoading()
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->columns([
                TextColumn::make('id')
                    ->label(__('site.table_id'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('first_name')
                    ->label(__('site.first_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label(__('site.last_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mobile')
                    ->label(__('site.mobile'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('site.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('site.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        IdentityRecord::PENDING => 'warning',
                        IdentityRecord::PAID => 'info',
                        IdentityRecord::COMPLETED => 'success',
                        IdentityRecord::REJECT => 'danger',
                        IdentityRecord::INPROGRESS => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        IdentityRecord::PENDING => __('site.pending'),
                        IdentityRecord::PAID => __('site.paid'),
                        IdentityRecord::COMPLETED => __('site.completed'),
                        IdentityRecord::REJECT => __('site.reject'),
                        IdentityRecord::INPROGRESS => __('site.in_progress'),
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y-m-d H:i:s') : null)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('site.updated_at'))
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y-m-d H:i:s') : null)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options([
                        IdentityRecord::PENDING => __('site.pending'),
                        IdentityRecord::PAID => __('site.paid'),
                        IdentityRecord::COMPLETED => __('site.completed'),
                        IdentityRecord::REJECT => __('site.reject'),
                        IdentityRecord::INPROGRESS => __('site.in_progress'),
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->label(__('site.created_at'))
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('site.from_date')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('site.to_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('site.view_identity_record')),
                Tables\Actions\EditAction::make()
                    ->label(__('site.edit_identity_record')),
                Tables\Actions\Action::make('approve')
                    ->label(__('site.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('site.confirm_approve_identity'))
                    ->modalDescription(__('site.confirm_approve_identity_description'))
                    ->modalSubmitActionLabel(__('site.approve'))
                    ->modalCancelActionLabel(__('site.cancel'))
                    ->visible(fn ($record) => in_array($record->status, [IdentityRecord::PENDING, IdentityRecord::PAID]))
                    ->action(function ($record) {
                        if ($record->status !== IdentityRecord::PAID) {
                            Notification::make()
                                ->title(__('site.identity_verification_payment_required'))
                                ->body(__('site.identity_verification_payment_required_message'))
                                ->danger()
                                ->send();

                            return;
                        }

                        try {
                            DB::beginTransaction();

                            // Use lazy loading for user update
                            $record->load('user');
                            $record->user->update([
                                'verified_at' => Carbon::now()
                            ]);

                            $record->update([
                                'status' => IdentityRecord::COMPLETED,
                            ]);

                            // Cache the user to avoid re-fetching
                            $user = $record->user;

                            // Add the default plan
                            app(SubscribeRepository::class)->createSubscription(
                                Plan::find(config('plan.default_plan_id')), $user
                            );

                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            throw $e;
                        }

                        Notification::make()
                            ->title(__('site.identity_record_approved_successfully'))
                            ->success()
                            ->send();
                    })
                    ->after(function ($record) {
                        // Use the already loaded user
                        $user = $record->user ?? User::find($record->user_id);

                        if ($user) {
                            NotificationService::create([
                                'title' => __('site.identity_verification_approved'),
                                'content' => __('site.identity_verification_approved_message'),
                                'id' => $user->id,
                                'type' => NotificationService::PROFILE,
                            ], $user);
                        }
                    }),
                Tables\Actions\Action::make('reject')
                    ->label(__('site.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, [IdentityRecord::PENDING, IdentityRecord::INPROGRESS,  IdentityRecord::PAID]))
                    ->requiresConfirmation()
                    ->modalHeading(__('site.confirm_reject_identity'))
                    ->modalDescription(__('site.confirm_reject_identity_description'))
                    ->modalSubmitActionLabel(__('site.reject'))
                    ->modalCancelActionLabel(__('site.cancel'))
                    ->action(function ($record) {
                        // Load user before deletion to avoid N+1 query
                        $record->load('user');
                        $user = $record->user;

                        $record->delete();

                        // Clear related caches
                        Cache::forget('identity_records_count');

                        // Send notification if user exists
                        if ($user) {
                            NotificationService::create([
                                'title' => __('site.identity_verification_rejected'),
                                'content' => __('site.identity_verification_rejected_message'),
                                'id' => $user->id,
                                'type' => NotificationService::PROFILE,
                            ], $user);
                        }

                        Notification::make()
                            ->title(__('site.identity_record_rejected_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIdentityRecords::route('/'),
            // 'create' => Pages\CreateIdentityRecord::route('/create'),
            'edit' => Pages\EditIdentityRecord::route('/{record}/edit'),
            'view' => Pages\ViewIdentityRecord::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Cache the count for better performance
        return Cache::remember('identity_records_count', 300, function () {
            return static::getModel()::count();
        });
    }

    /**
     * Optimize the Eloquent query for better performance
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select([
                'id', 'first_name', 'last_name', 'national_code', 'mobile', 'email',
                'status', 'user_id', 'country_id', 'province_id', 'city_id',
                'image_national_code_front', 'image_national_code_back', 'video',
                'created_at', 'updated_at'
            ])
            ->with([
                'user:id,email,first_name,last_name',
                'country:id,title',
                'province:id,title',
                'city:id,title'
            ]);
    }

    /**
     * Get table query string identifier for better caching
     */
    public static function getTableQueryStringIdentifier(): string
    {
        return 'identity_records';
    }

    /**
     * Cache expensive status options
     */
    public static function getStatusOptions(): array
    {
        return Cache::remember('identity_record_status_options', 3600, function () {
            return [
                IdentityRecord::PENDING => __('site.pending'),
                IdentityRecord::PAID => __('site.paid'),
                IdentityRecord::COMPLETED => __('site.completed'),
                IdentityRecord::REJECT => __('site.reject'),
                IdentityRecord::INPROGRESS => __('site.in_progress'),
            ];
        });
    }

    /**
     * Cache country options for better performance
     */
    public static function getCountryOptions(): array
    {
        return Cache::remember('country_options', 3600, function () {
            return \Domain\Address\Models\Country::select('id', 'title')
                ->orderBy('title')
                ->pluck('title', 'id')
                ->toArray();
        });
    }

    /**
     * Cache province options for better performance
     */
    public static function getProvinceOptions(): array
    {
        return Cache::remember('province_options', 3600, function () {
            return \Domain\Address\Models\Province::select('id', 'title')
                ->orderBy('title')
                ->pluck('title', 'id')
                ->toArray();
        });
    }

    /**
     * Cache city options for better performance
     */
    public static function getCityOptions(): array
    {
        return Cache::remember('city_options', 3600, function () {
            return \Domain\Address\Models\City::select('id', 'title')
                ->orderBy('title')
                ->pluck('title', 'id')
                ->toArray();
        });
    }

    /**
     * Clear all related caches
     */
    public static function clearCaches(): void
    {
        Cache::forget('identity_records_count');
        Cache::forget('identity_record_status_options');
        Cache::forget('country_options');
        Cache::forget('province_options');
        Cache::forget('city_options');
        Cache::forget('default_plan');
    }

    /**
     * Get table performance configuration
     */
    public static function getTablePerformanceConfig(): array
    {
        return [
            'defer_loading' => true,
            'persist_filters' => true,
            'persist_sort' => true,
            'lazy_image_loading' => true,
            'default_pagination' => 25,
            'max_pagination' => 100,
        ];
    }
}