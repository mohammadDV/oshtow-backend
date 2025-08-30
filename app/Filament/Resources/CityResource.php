<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use Domain\Address\Models\City;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Morilog\Jalali\Jalalian;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Address';

    protected static ?int $navigationSort = 18;

    public static function getNavigationLabel(): string
    {
        return __('site.cities');
    }

    public static function getModelLabel(): string
    {
        return __('site.city');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.cities');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.Address Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('site.city_information'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('site.title'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('province_id')
                            ->label(__('site.province'))
                            ->relationship('province', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label(__('site.status'))
                            ->options([
                                1 => __('site.Active'),
                                0 => __('site.Inactive'),
                            ])
                            ->default(1)
                            ->required(),
                        Forms\Components\TextInput::make('priority')
                            ->label(__('site.priority'))
                            ->numeric()
                            ->default(0)
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
                TextColumn::make('province.title')
                    ->label(__('site.province'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('province.country.title')
                    ->label(__('site.country'))
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
                TextColumn::make('priority')
                    ->label(__('site.priority'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y-m-d H:i:s') : null)
                    ->sortable()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('province_id')
                    ->label(__('site.province'))
                    ->relationship('province', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('province.country_id')
                    ->label(__('site.country'))
                    ->relationship('province.country', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options([
                        1 => __('site.Active'),
                        0 => __('site.Inactive'),
                    ]),
                Tables\Filters\Filter::make('priority')
                    ->label(__('site.priority'))
                    ->form([
                        Forms\Components\TextInput::make('priority_from')
                            ->label(__('site.from_priority'))
                            ->numeric(),
                        Forms\Components\TextInput::make('priority_until')
                            ->label(__('site.to_priority'))
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['priority_from'],
                                fn (Builder $query, $priority): Builder => $query->where('priority', '>=', $priority),
                            )
                            ->when(
                                $data['priority_until'],
                                fn (Builder $query, $priority): Builder => $query->where('priority', '<=', $priority),
                            );
                    }),
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
                //     ->label(__('site.view_city')),
                Tables\Actions\EditAction::make()
                    ->label(__('site.edit_city')),
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
            'index' => Pages\ListCities::route('/'),
            'create' => Pages\CreateCity::route('/create'),
            'edit' => Pages\EditCity::route('/{record}/edit'),
            'view' => Pages\ViewCity::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
