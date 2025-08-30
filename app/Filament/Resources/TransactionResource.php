<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use Domain\Payment\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Morilog\Jalali\Jalalian;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Financial';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('site.transactions');
    }

    public static function getModelLabel(): string
    {
        return __('site.transaction');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.transactions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.Payment Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('site.transaction_information'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('site.user'))
                            ->relationship('user', 'nickname')
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->required(),
                        Forms\Components\TextInput::make('model_id')
                            ->label(__('site.model_id'))
                            ->disabled()
                            ->required(),
                        Forms\Components\TextInput::make('model_type')
                            ->label(__('site.model_type'))
                            ->disabled()
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label(__('site.amount'))
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\Select::make('status')
                            ->label(__('site.status'))
                            ->options([
                                Transaction::PENDING => __('site.pending'),
                                Transaction::COMPLETED => __('site.completed'),
                                Transaction::CANCELLED => __('site.cancelled'),
                                Transaction::FAILED => __('site.failed'),
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('reference')
                            ->label(__('site.reference'))
                            ,
                        Forms\Components\TextInput::make('bank_transaction_id')
                            ->label(__('site.bank_transaction_id'))
                            ,
                        Forms\Components\Textarea::make('description')
                            ->label(__('site.description'))
                            ->rows(3),
                        Forms\Components\Textarea::make('message')
                            ->label(__('site.message'))
                            ->rows(3),
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
                TextColumn::make('user.nickname')
                    ->label(__('site.user'))
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => route('filament.admin.resources.users.view', ['record' => $record->user_id]))
                    ->openUrlInNewTab(),
                TextColumn::make('amount')
                    ->label(__('site.amount'))
                    ->money('IRR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('site.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Transaction::COMPLETED => 'success',
                        Transaction::PENDING => 'warning',
                        Transaction::FAILED => 'danger',
                        Transaction::CANCELLED => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Transaction::COMPLETED => __('site.completed'),
                        Transaction::PENDING => __('site.pending'),
                        Transaction::FAILED => __('site.failed'),
                        Transaction::CANCELLED => __('site.cancelled'),
                        default => $state,
                    }),
                TextColumn::make('reference')
                    ->label(__('site.reference'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('bank_transaction_id')
                    ->label(__('site.bank_transaction_id'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('model_type')
                    ->label(__('site.model_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Transaction::WALLET => 'blue',
                        Transaction::PLAN => 'green',
                        Transaction::IDENTITY => 'purple',
                        Transaction::SECURE => 'orange',
                        default => 'gray',
                    }),
                TextColumn::make('model_id')
                    ->label(__('site.model_id'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->formatStateUsing(fn ($state) => Jalalian::fromDateTime($state)->format('Y/m/d H:i:s'))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('site.user'))
                    ->relationship('user', 'nickname')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options([
                        Transaction::PENDING => __('site.pending'),
                        Transaction::COMPLETED => __('site.completed'),
                        Transaction::CANCELLED => __('site.cancelled'),
                        Transaction::FAILED => __('site.failed'),
                    ]),
                Tables\Filters\SelectFilter::make('model_type')
                    ->label(__('site.model_type'))
                    ->options([
                        Transaction::WALLET => __('site.wallet'),
                        Transaction::PLAN => __('site.plan'),
                        Transaction::IDENTITY => __('site.identity'),
                        Transaction::SECURE => __('site.secure'),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // No bulk actions - no delete operations
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
            'index' => Pages\ListTransactions::route('/'),
            'view' => Pages\ViewTransaction::route('/{record}'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}