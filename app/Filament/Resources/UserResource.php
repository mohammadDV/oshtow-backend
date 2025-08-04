<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use Domain\User\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Morilog\Jalali\Jalalian;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    public static function getNavigationLabel(): string
    {
        return __('site.users');
    }

    public static function getModelLabel(): string
    {
        return __('site.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.users');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.User Management');
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
                        Forms\Components\TextInput::make('nickname')
                            ->label(__('site.nickname'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label(__('site.email'))
                            ->email()
                            ->disabled()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('mobile')
                            ->label(__('site.mobile'))
                            ->maxLength(15),
                        Forms\Components\TextInput::make('password')
                            ->label(__('site.Password'))
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        Forms\Components\Select::make('status')
                            ->label(__('site.status'))
                            ->options([
                                1 => __('site.Active'),
                                0 => __('site.Inactive'),
                            ])
                            ->default(1)
                            ->required(),
                        Forms\Components\Select::make('level')
                            ->label(__('site.level'))
                            ->options([
                                0 => __('site.user_level_1'),
                                3 => __('site.user_level_3'),
                            ])
                            ->default(1)
                            ->required(),
                        Forms\Components\Toggle::make('is_private')
                            ->label(__('site.is_private'))
                            ->default(false),
                        Forms\Components\Toggle::make('is_report')
                            ->label(__('site.is_report'))
                            ->default(false),
                    ])->columns(2),
                Forms\Components\Section::make(__('site.images'))
                    ->schema([
                        Forms\Components\FileUpload::make('profile_photo_path')
                            ->label(__('site.profile_photo_path'))
                            ->placeholder(__('site.upload_profile_photo'))
                            ->image()
                            ->imageEditor()
                            ->disk('s3')
                            ->directory('/users/profile-photos')
                            // ->previewable(false)
                            ->required(),
                        Forms\Components\FileUpload::make('bg_photo_path')
                            ->label(__('site.bg_photo_path'))
                            ->placeholder(__('site.upload_bg_photo'))
                            ->disk(config('app.env') === 'local' ? 's3_proxy' : 's3')
                            ->visibility('public')
                            ->image()
                            ->directory('/users/bg-photos'),
                    ])->columns(2),

                Forms\Components\Section::make(__('site.additional_information'))
                    ->schema([
                        Forms\Components\TextInput::make('point')
                            ->label(__('site.point'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('rate')
                            ->label(__('site.rate'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('customer_number')
                            ->label(__('site.customer_number'))
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('google_id')
                            ->label(__('site.google_id'))
                            ->maxLength(255),
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
                ImageColumn::make('profile_photo_path')
                    ->label(__('site.profile_photo_path'))
                    ->disk(config('app.env') === 'local' ? 's3_proxy' : 's3')
                    ->circular()
                    ->size(40),
                TextColumn::make('first_name')
                    ->label(__('site.first_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label(__('site.last_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nickname')
                    ->label(__('site.nickname'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('site.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mobile')
                    ->label(__('site.mobile'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('site.status'))
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'success',
                        0 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => $state === 1 ? __('site.Active') : __('site.Inactive')),
                TextColumn::make('level')
                    ->label(__('site.level'))
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'gray',
                        2 => 'blue',
                        3 => 'green',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => match($state) {
                        1 => __('site.user_level_1'),
                        2 => __('site.user_level_2'),
                        3 => __('site.user_level_3'),
                        default => __('site.user_level_1'),
                    }),
                TextColumn::make('point')
                    ->label(__('site.point'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y-m-d H:i:s') : null)
                    ->sortable(),
                TextColumn::make('verified_at')
                    ->label(__('site.verified_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y-m-d H:i:s') : null)
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->label(__('site.email_verified_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y-m-d H:i:s') : null)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options([
                        1 => __('site.Active'),
                        0 => __('site.Inactive'),
                    ]),
                Tables\Filters\SelectFilter::make('level')
                    ->label(__('site.level'))
                    ->options([
                        1 => __('site.user_level_1'),
                        2 => __('site.user_level_2'),
                        3 => __('site.user_level_3'),
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
                    ->label(__('site.view_user')),
                Tables\Actions\EditAction::make()
                    ->label(__('site.edit_user')),
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn ($record) => $record->status === 1 ? __('site.disable_user') : __('site.enable_user'))
                    ->icon(fn ($record) => $record->status === 1 ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->status === 1 ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->status === 1 ? __('site.confirm_disable_user') : __('site.confirm_enable_user'))
                    ->modalDescription(fn ($record) => $record->status === 1 ? __('site.confirm_disable_user_description') : __('site.confirm_enable_user_description'))
                    ->modalSubmitActionLabel(fn ($record) => $record->status === 1 ? __('site.disable_user') : __('site.enable_user'))
                    ->modalCancelActionLabel(__('site.cancel'))
                    ->action(function ($record) {
                        $newStatus = $record->status === 1 ? 0 : 1;
                        $record->update(['status' => $newStatus]);
                    })
                    ->after(function ($record) {
                        $message = $record->status === 1 ? __('site.user_enabled_successfully') : __('site.user_disabled_successfully');
                        \Filament\Notifications\Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                // Delete actions removed to disable user deletion
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
