<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Filament\Resources\ReviewResource\RelationManagers;
use Domain\Review\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Morilog\Jalali\Jalalian;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Reviews';

    public static function getNavigationLabel(): string
    {
        return __('site.reviews');
    }

    public static function getModelLabel(): string
    {
        return __('site.review');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.reviews');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.Reviews');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('site.review_information'))
                    ->schema([
                        Forms\Components\Textarea::make('comment')
                            ->label(__('site.comment'))
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('rate')
                            ->label(__('site.rate'))
                            ->options([
                                1 => '1 ' . __('site.star'),
                                2 => '2 ' . __('site.stars'),
                                3 => '3 ' . __('site.stars'),
                                4 => '4 ' . __('site.stars'),
                                5 => '5 ' . __('site.stars'),
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label(__('site.status'))
                            ->options([
                                0 => __('site.Inactive'),
                                1 => __('site.Active'),
                            ])
                            ->default(1)
                            ->required(),
                        Forms\Components\Select::make('claim_id')
                            ->label(__('site.claim'))
                            ->relationship('claim', 'id')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('owner_id')
                            ->label(__('site.owner'))
                            ->relationship('owner', 'nickname')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->label(__('site.user'))
                            ->relationship('user', 'nickname')
                            ->searchable()
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('site.id'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('comment')
                    ->label(__('site.comment'))
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rate')
                    ->label(__('site.rate'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'danger',
                        '2' => 'warning',
                        '3' => 'gray',
                        '4' => 'info',
                        '5' => 'success',
                    }),
                IconColumn::make('status')
                    ->label(__('site.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('claim.id')
                    ->label(__('site.claim'))
                    ->sortable()
                    ->searchable()
                    ->url(fn ($record) => $record->claim ? route('filament.admin.resources.claims.view', $record->claim) : null)
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn ($record) => $record->claim ? $record->claim->id : 'N/A'),
                TextColumn::make('owner.nickname')
                    ->label(__('site.owner'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.nickname')
                    ->label(__('site.user'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => Jalalian::fromDateTime($state)->format('Y/m/d H:i:s'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('site.updated_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => Jalalian::fromDateTime($state)->format('Y/m/d H:i:s'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options([
                        0 => __('site.Inactive'),
                        1 => __('site.Active'),
                    ]),
                Tables\Filters\SelectFilter::make('rate')
                    ->label(__('site.rate'))
                    ->options([
                        1 => '1 ' . __('site.star'),
                        2 => '2 ' . __('site.stars'),
                        3 => '3 ' . __('site.stars'),
                        4 => '4 ' . __('site.stars'),
                        5 => '5 ' . __('site.stars'),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ClaimRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}