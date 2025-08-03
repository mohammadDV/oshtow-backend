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
use Morilog\Jalali\Jalalian;
use Filament\Notifications\Notification;

class IdentityRecordResource extends Resource
{
    protected static ?string $model = IdentityRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Identity Management';

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
                            ->relationship('country', 'title')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('province_id')
                            ->label(__('site.province'))
                            ->relationship('province', 'title')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('city_id')
                            ->label(__('site.city'))
                            ->relationship('city', 'title')
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
                            ->image()
                            ->required()
                            ->disk(config('app.env') === 'local' ? 's3_proxy' : 's3')
                            ->directory('/identity-records/national-code-front'),
                        Forms\Components\FileUpload::make('image_national_code_back')
                            ->label(__('site.national_code_back'))
                            ->image()
                            ->required()
                            ->disk(config('app.env') === 'local' ? 's3_proxy' : 's3')
                            ->directory('/identity-records/national-code-back'),
                        Forms\Components\FileUpload::make('video')
                            ->label(__('site.video'))
                            ->acceptedFileTypes(['video/*'])
                            ->required()
                            ->disk(config('app.env') === 'local' ? 's3_proxy' : 's3')
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
                            ])
                            ->default(IdentityRecord::PENDING)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('site.table_id'))
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('image_national_code_front')
                    ->label(__('site.national_code_front'))
                    ->disk(config('app.env') === 'local' ? 's3_proxy' : 's3')
                    ->size(40),
                TextColumn::make('first_name')
                    ->label(__('site.first_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label(__('site.last_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('national_code')
                    ->label(__('site.national_code'))
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
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        IdentityRecord::PENDING => __('site.pending'),
                        IdentityRecord::PAID => __('site.paid'),
                        IdentityRecord::COMPLETED => __('site.completed'),
                        IdentityRecord::REJECT => __('site.reject'),
                        default => $state,
                    }),
                TextColumn::make('user.email')
                    ->label(__('site.user'))
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->user ? route('filament.admin.resources.users.view', $record->user) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y-m-d H:i:s') : null)
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('site.updated_at'))
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y-m-d H:i:s') : null)
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
                    ->visible(fn ($record) => $record->status === IdentityRecord::PENDING)
                    ->requiresConfirmation()
                    ->modalHeading(__('site.confirm_approve_identity'))
                    ->modalDescription(__('site.confirm_approve_identity_description'))
                    ->modalSubmitActionLabel(__('site.approve'))
                    ->modalCancelActionLabel(__('site.cancel'))
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
                            $record->user->update([
                                'verified_at' => Carbon::now()
                            ]);

                            $record->update([
                                'status' => IdentityRecord::COMPLETED,
                            ]);

                            $user = User::find($record->user_id);

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
                        $user = User::find($record->user_id);
                        NotificationService::create([
                            'title' => __('site.identity_verification_approved'),
                            'content' => __('site.identity_verification_approved_message'),
                            'id' => $user->id,
                            'type' => NotificationService::PROFILE,
                        ], $user);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label(__('site.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->status, [IdentityRecord::PENDING, IdentityRecord::PAID]))
                    ->requiresConfirmation()
                    ->modalHeading(__('site.confirm_reject_identity'))
                    ->modalDescription(__('site.confirm_reject_identity_description'))
                    ->modalSubmitActionLabel(__('site.reject'))
                    ->modalCancelActionLabel(__('site.cancel'))
                    ->action(function ($record) {
                        $record->delete();
                    })
                    ->after(function ($record) {
                        $user = User::find($record->user_id);
                        NotificationService::create([
                            'title' => __('site.identity_verification_rejected'),
                            'content' => __('site.identity_verification_rejected_message'),
                            'id' => $user->id,
                            'type' => NotificationService::PROFILE,
                        ], $user);
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
            'create' => Pages\CreateIdentityRecord::route('/create'),
            'edit' => Pages\EditIdentityRecord::route('/{record}/edit'),
            'view' => Pages\ViewIdentityRecord::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}