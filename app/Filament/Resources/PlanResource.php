<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;

/**
 * Plan Resource
 *
 * Note: Admins can only view and edit plans. Delete actions are disabled
 * because plans are critical system configurations that should not be removed.
 * This ensures data integrity and prevents accidental deletion of important plan data.
 */
class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Subscription';

    protected static ?int $navigationSort = 11;

    public static function getNavigationLabel(): string
    {
        return __('site.plans');
    }

    public static function getModelLabel(): string
    {
        return __('site.plan');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.plans');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.Subscription Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('site.plan_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('site.plan_title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('site.enter_plan_title')),
                                Select::make('priod')
                                    ->label(__('site.plan_period'))
                                    ->options([
                                        'monthly' => __('site.monthly'),
                                        'yearly' => __('site.yearly'),
                                    ])
                                    ->required()
                                    ->default('monthly'),
                                TextInput::make('amount')
                                    ->label(__('site.plan_amount'))
                                    ->numeric()
                                    ->prefix(__('site.currency'))
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(999999999999)
                                    ->placeholder(__('site.enter_plan_amount')),
                                TextInput::make('period_count')
                                    ->label(__('site.plan_period_count'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->placeholder(__('site.enter_plan_period_count')),
                                TextInput::make('claim_count')
                                    ->label(__('site.plan_claim_count'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(1000)
                                    ->default(0)
                                    ->placeholder(__('site.enter_plan_claim_count')),
                                TextInput::make('project_count')
                                    ->label(__('site.plan_project_count'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(1000)
                                    ->default(0)
                                    ->placeholder(__('site.enter_plan_project_count')),
                                Toggle::make('status')
                                    ->label(__('site.plan_status'))
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->onIcon('heroicon-s-check')
                                    ->offIcon('heroicon-s-x-mark')
                                    ->default(0),
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
                TextColumn::make('title')
                    ->label(__('site.plan_title'))
                    ->sortable()
                    ->searchable()
                    ->color('primary')
                    ->icon('heroicon-o-credit-card'),
                TextColumn::make('amount')
                    ->label(__('site.plan_amount'))
                    ->money('IRR')
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('priod')
                    ->label(__('site.plan_period'))
                    ->colors([
                        'info' => 'monthly',
                        'warning' => 'yearly',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'monthly' => __('site.monthly'),
                        'yearly' => __('site.yearly'),
                        default => $state,
                    }),
                TextColumn::make('subscriptions_count')
                    ->label(__('site.subscriptions_count'))
                    ->counts('subscriptions')
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('status')
                    ->label(__('site.plan_status'))
                    ->colors([
                        'danger' => 0,
                        'success' => 1,
                    ])
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => __('site.inactive'),
                        1 => __('site.active'),
                        default => $state,
                    }),
                TextColumn::make('user.nickname')
                    ->label(__('site.plan_creator'))
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->user ? route('filament.admin.resources.users.view', $record->user) : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->icon('heroicon-o-user')
                    ->tooltip(__('site.click_to_view_user_details')),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('site.plan_status'))
                    ->options([
                        0 => __('site.inactive'),
                        1 => __('site.active'),
                    ]),
                SelectFilter::make('priod')
                    ->label(__('site.plan_period'))
                    ->options([
                        'monthly' => __('site.monthly'),
                        'yearly' => __('site.yearly'),
                    ]),
                Filter::make('active_plans')
                    ->label(__('site.active_plans'))
                    ->query(fn (Builder $query): Builder => $query->where('status', 1)),
                Filter::make('inactive_plans')
                    ->label(__('site.inactive_plans'))
                    ->query(fn (Builder $query): Builder => $query->where('status', 0)),
            ])
            ->actions([
                // ViewAction::make()
                //     ->label(__('site.view_plan')),
                // EditAction::make()
                //     ->label(__('site.edit_plan')),
            ])
            ->bulkActions([
                // Bulk actions removed - admins cannot delete plans
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
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'view' => Pages\ViewPlan::route('/{record}'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
