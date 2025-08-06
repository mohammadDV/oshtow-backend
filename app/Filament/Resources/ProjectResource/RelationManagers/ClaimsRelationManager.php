<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Domain\Claim\Models\Claim;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;

class ClaimsRelationManager extends RelationManager
{
    protected static string $relationship = 'claims';

    protected static ?string $recordTitleAttribute = 'description';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('IRR'),
                Forms\Components\TextInput::make('weight')
                    ->numeric()
                    ->suffix('kg'),
                Forms\Components\Textarea::make('address')
                    ->maxLength(500),
                Forms\Components\Select::make('status')
                    ->options([
                        Claim::PENDING => __('site.pending'),
                        Claim::APPROVED => __('site.approved'),
                        Claim::PAID => __('site.paid'),
                        Claim::INPROGRESS => __('site.in_progress'),
                        Claim::DELIVERED => __('site.delivered'),
                        Claim::CANCELED => __('site.canceled'),
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                TextColumn::make('id')
                    ->label(__('site.table_id'))
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('image')
                    ->label(__('site.image'))
                    ->url(fn ($record) => $record->image ? $record->image : null)
                    ->circular()
                    ->size(40),
                TextColumn::make('description')
                    ->label(__('site.description'))
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('amount')
                    ->label(__('site.amount'))
                    ->money('IRR')
                    ->sortable(),
                TextColumn::make('weight')
                    ->label(__('site.weight'))
                    ->suffix(' kg')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label(__('site.status'))
                    ->colors([
                        'warning' => Claim::PENDING,
                        'success' => Claim::APPROVED,
                        'primary' => Claim::PAID,
                        'info' => Claim::INPROGRESS,
                        'success' => Claim::DELIVERED,
                        'danger' => Claim::CANCELED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Claim::PENDING => __('site.pending'),
                        Claim::APPROVED => __('site.approved'),
                        Claim::PAID => __('site.paid'),
                        Claim::INPROGRESS => __('site.in_progress'),
                        Claim::DELIVERED => __('site.delivered'),
                        Claim::CANCELED => __('site.canceled'),
                        default => $state,
                    }),
                TextColumn::make('user.nickname')
                    ->label(__('site.claim_user'))
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->user ? route('filament.admin.resources.users.edit', $record->user) : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->icon('heroicon-o-user'),
                TextColumn::make('sponsor.nickname')
                    ->label(__('site.sponsor'))
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->sponsor ? route('filament.admin.resources.users.edit', $record->sponsor) : null)
                    ->openUrlInNewTab()
                    ->color('secondary')
                    ->icon('heroicon-o-user-group'),
                TextColumn::make('address')
                    ->label(__('site.address'))
                    ->limit(30)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options([
                        Claim::PENDING => __('site.pending'),
                        Claim::APPROVED => __('site.approved'),
                        Claim::PAID => __('site.paid'),
                        Claim::INPROGRESS => __('site.in_progress'),
                        Claim::DELIVERED => __('site.delivered'),
                        Claim::CANCELED => __('site.canceled'),
                    ]),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('site.view_claim')),
                // Tables\Actions\EditAction::make()
                //     ->label(__('site.edit_claim')),
            ])
            ->defaultSort('created_at', 'desc');
    }
}