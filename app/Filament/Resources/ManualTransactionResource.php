<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManualTransactionResource\Pages;
use Domain\IdentityRecord\Models\IdentityRecord;
use Domain\Payment\Models\Transaction;
use Domain\Wallet\Models\Wallet;
use Domain\Wallet\Models\WalletTransaction;
use Domain\Notification\Services\NotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class ManualTransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $slug = 'manual-transactions';

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Payment Management';

    public static function getNavigationLabel(): string
    {
        return __('site.manual_transactions');
    }

    public static function getModelLabel(): string
    {
        return __('site.manual_transaction');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.manual_transactions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.Payment Management');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationSort(): int
    {
        return 10;
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
                            ->label(__('site.reference')),
                        Forms\Components\TextInput::make('bank_transaction_id')
                            ->label(__('site.bank_transaction_id')),
                        Forms\Components\Textarea::make('description')
                            ->label(__('site.description'))
                            ->rows(3),
                        Forms\Components\Textarea::make('message')
                            ->label(__('site.message'))
                            ->rows(3),
                        Forms\Components\Toggle::make('manual')
                            ->label(__('site.manual'))
                            ->disabled()
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                try {
                    return $query->where('manual', 1);
                } catch (\Exception $e) {
                    return $query;
                }
            })
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete')
                    ->label(__('site.complete_transaction'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('site.complete_transaction'))
                    ->modalDescription(__('site.complete_transaction_description'))
                    ->modalSubmitActionLabel(__('site.confirm_completion'))
                    ->modalCancelActionLabel(__('site.cancel'))
                    ->visible(fn (Transaction $record): bool => $record->status === Transaction::PENDING)
                    ->action(function (Transaction $record): void {
                        try {
                            DB::beginTransaction();

                            // Update transaction status
                            $record->update(['status' => Transaction::COMPLETED]);

                            if ($record->model_type == Transaction::IDENTITY) {
                                $identityRecord = IdentityRecord::find($record->model_id);
                                $identityRecord->status = IdentityRecord::PAID;
                                $identityRecord->save();
                                DB::commit();
                            } else {

                                $wallet = Wallet::where('user_id', $record->user_id)
                                    ->where('currency', Wallet::IRR)
                                    ->where('status', 1)
                                    ->first();

                                if ($wallet) {
                                    // Calculate 90% of the amount
                                    $walletAmount = $record->amount * 0.9;

                                    // Create wallet transaction and update balance
                                    WalletTransaction::createTransaction(
                                        wallet: $wallet,
                                        amount: $walletAmount,
                                        type: WalletTransaction::DEPOSITE,
                                        description: __('site.manual_transaction_completed_description', [
                                            'transaction_id' => $record->id,
                                            'amount' => number_format($record->amount),
                                            'wallet_amount' => number_format($walletAmount)
                                        ]),
                                        status: WalletTransaction::COMPLETED
                                    );

                                    // Send notification to user
                                    NotificationService::create([
                                        'title' => __('site.manual_transaction_completed_title'),
                                        'content' => __('site.manual_transaction_completed_content', [
                                            'amount' => number_format($record->amount),
                                            'wallet_amount' => number_format($walletAmount)
                                        ]),
                                        'id' => $wallet->id,
                                        'type' => NotificationService::WALLET,
                                    ], $record->user);

                                    DB::commit();

                                    Notification::make()
                                        ->title(__('site.transaction_completed_successfully'))
                                        ->body(__('site.wallet_credited_successfully', [
                                            'amount' => number_format($walletAmount),
                                            'currency' => $wallet->currency
                                        ]))
                                        ->success()
                                        ->send();
                                } else {
                                    DB::rollBack();
                                    Notification::make()
                                        ->title(__('site.wallet_not_found'))
                                        ->body(__('site.cannot_credit_wallet'))
                                        ->danger()
                                        ->send();
                                }
                            }

                            // Get user's wallet

                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title(__('site.error_occurred'))
                                ->body(__('site.transaction_completion_failed'))
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('fail')
                    ->label(__('site.fail_transaction'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('site.fail_transaction'))
                    ->modalDescription(__('site.fail_transaction_description'))
                    ->modalSubmitActionLabel(__('site.confirm_failure'))
                    ->modalCancelActionLabel(__('site.cancel'))
                    ->visible(fn (Transaction $record): bool => $record->status === Transaction::PENDING)
                    ->action(function (Transaction $record): void {
                        try {
                            // Update transaction status
                            $record->update(['status' => Transaction::FAILED]);

                            // Send notification to user
                            NotificationService::create([
                                'title' => __('site.manual_transaction_failed_title'),
                                'content' => __('site.manual_transaction_failed_content', [
                                    'amount' => number_format($record->amount),
                                    'transaction_id' => $record->id
                                ]),
                                'id' => $record->id,
                                'type' => NotificationService::WALLET,
                            ], $record->user);

                            Notification::make()
                                ->title(__('site.transaction_failed_successfully'))
                                ->body(__('site.user_notified_of_failure'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('site.error_occurred'))
                                ->body(__('site.transaction_failure_update_failed'))
                                ->danger()
                                ->send();
                        }
                    }),
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
            'index' => Pages\ListManualTransactions::route('/'),
            'view' => Pages\ViewManualTransaction::route('/{record}'),
            'edit' => Pages\EditManualTransaction::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            return static::getModel()::where('manual', 1)->count();
        } catch (\Exception $e) {
            return null;
        }
    }
}
