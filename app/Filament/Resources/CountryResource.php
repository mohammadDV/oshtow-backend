<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use Domain\Address\Models\Country;
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

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Address Management';

    public static function getNavigationLabel(): string
    {
        return __('site.countries');
    }

    public static function getModelLabel(): string
    {
        return __('site.country');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.countries');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.Address Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('site.country_information'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('site.title'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('image')
                            ->label(__('site.image'))
                            ->image()
                            ->disk('s3')
                            ->directory('countries')
                            ->maxSize(2048),
                        Forms\Components\Select::make('status')
                            ->label(__('site.status'))
                            ->options([
                                1 => __('site.Active'),
                                0 => __('site.Inactive'),
                            ])
                            ->default(1)
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
                TextColumn::make('title')
                    ->label(__('site.title'))
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('image')
                    ->label(__('site.image'))
                    ->disk('s3')
                    ->circular()
                    ->size(40),
                TextColumn::make('status')
                    ->label(__('site.status'))
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'success',
                        0 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => $state === 1 ? __('site.Active') : __('site.Inactive')),
                TextColumn::make('provinces_count')
                    ->label(__('site.provinces_count'))
                    ->counts('provinces')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y-m-d H:i:s') : null)
                    ->sortable()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options([
                        1 => __('site.Active'),
                        0 => __('site.Inactive'),
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
                // Tables\Actions\ViewAction::make()
                //     ->label(__('site.view_country')),
                Tables\Actions\EditAction::make()
                    ->label(__('site.edit_country')),
                // Delete action removed to prevent deletion
            ])
            ->bulkActions([
                // Bulk delete actions removed to prevent deletion
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
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
            'view' => Pages\ViewCountry::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}