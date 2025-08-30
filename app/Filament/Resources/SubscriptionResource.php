<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use Domain\Plan\Models\Subscription;
use Domain\Plan\Models\Plan;
use Domain\User\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;

/**
 * Subscription Resource
 *
 * Note: Admins can only view and edit subscriptions. Delete actions are disabled
 * because subscriptions are critical user data that should not be removed.
 * This ensures data integrity and prevents accidental deletion of important subscription data.
 */
class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Subscription';

    protected static ?int $navigationSort = 12;

    public static function getNavigationLabel(): string
    {
        return __('site.subscriptions');
    }

    public static function getModelLabel(): string
    {
        return __('site.subscription');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.subscriptions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.Subscription Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('site.subscription_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('plan_id')
                                    ->label(__('site.subscription_plan'))
                                    ->options(Plan::pluck('title', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->relationship('plan', 'title'),
                                Select::make('user_id')
                                    ->label(__('site.subscription_user'))
                                    ->options(User::pluck('nickname', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->relationship('user', 'nickname'),
                                DateTimePicker::make('ends_at')
                                    ->label(__('site.subscription_ends_at'))
                                    ->required()
                                    ->placeholder(__('site.enter_subscription_ends_at')),
                                TextInput::make('claim_count')
                                    ->label(__('site.subscription_claim_count'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(1000)
                                    ->default(0)
                                    ->placeholder(__('site.enter_subscription_claim_count')),
                                TextInput::make('project_count')
                                    ->label(__('site.subscription_project_count'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(1000)
                                    ->default(0)
                                    ->placeholder(__('site.enter_subscription_project_count')),
                                Toggle::make('active')
                                    ->label(__('site.subscription_active'))
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->onIcon('heroicon-s-check')
                                    ->offIcon('heroicon-s-x-mark')
                                    ->default(1),
                            ]),
                    ])->columns(1),
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
                TextColumn::make('plan.title')
                    ->label(__('site.subscription_plan'))
                    ->sortable()
                    ->searchable()
                    ->color('primary')
                    ->icon('heroicon-o-credit-card')
                    ->url(fn ($record) => $record->plan ? route('filament.admin.resources.plans.view', $record->plan) : null)
                    ->openUrlInNewTab()
                    ->tooltip(__('site.click_to_view_plan_details')),
                TextColumn::make('user.nickname')
                    ->label(__('site.subscription_user'))
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->icon('heroicon-o-user')
                    ->url(fn ($record) => $record->user ? route('filament.admin.resources.users.view', $record->user) : null)
                    ->openUrlInNewTab()
                    ->tooltip(__('site.click_to_view_user_details')),
                TextColumn::make('ends_at')
                    ->label(__('site.subscription_ends_at'))
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->color(fn ($record) => $record->ends_at && $record->ends_at->isPast() ? 'danger' : 'success'),
                TextColumn::make('claim_count')
                    ->label(__('site.subscription_claim_count'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('project_count')
                    ->label(__('site.subscription_project_count'))
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('active')
                    ->label(__('site.subscription_active'))
                    ->colors([
                        'danger' => 0,
                        'success' => 1,
                    ])
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => __('site.inactive'),
                        1 => __('site.active'),
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('active')
                    ->label(__('site.subscription_active'))
                    ->options([
                        0 => __('site.inactive'),
                        1 => __('site.active'),
                    ]),
                SelectFilter::make('plan_id')
                    ->label(__('site.subscription_plan'))
                    ->options(Plan::pluck('title', 'id'))
                    ->searchable(),
                SelectFilter::make('user_id')
                    ->label(__('site.subscription_user'))
                    ->options(User::pluck('nickname', 'id'))
                    ->searchable(),
                Filter::make('active_subscriptions')
                    ->label(__('site.active_subscriptions'))
                    ->query(fn (Builder $query): Builder => $query->where('active', 1)),
                Filter::make('inactive_subscriptions')
                    ->label(__('site.inactive_subscriptions'))
                    ->query(fn (Builder $query): Builder => $query->where('active', 0)),
                Filter::make('expired_subscriptions')
                    ->label(__('site.expired_subscriptions'))
                    ->query(fn (Builder $query): Builder => $query->where('ends_at', '<', now())),
                Filter::make('active_subscriptions')
                    ->label(__('site.active_subscriptions'))
                    ->query(fn (Builder $query): Builder => $query->where('ends_at', '>', now())),
            ])
            ->actions([
                // ViewAction::make()
                //     ->label(__('site.view_subscription')),
                // EditAction::make()
                //     ->label(__('site.edit_subscription')),
            ])
            ->bulkActions([
                // Bulk actions removed - admins cannot delete subscriptions
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
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'view' => Pages\ViewSubscription::route('/{record}'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
