<?php

namespace App\Filament\Resources\ReviewResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Morilog\Jalali\Jalalian;
use Filament\Forms\Components\TextInput;

class ClaimRelationManager extends RelationManager
{
    protected static string $relationship = 'claim';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('id')
                    ->label(__('site.id'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label(__('site.id'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->label(__('site.description'))
                    ->limit(50)
                    ->searchable(),
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
                        'warning' => 'pending',
                        'success' => 'approved',
                        'info' => 'paid',
                        'primary' => 'in_progress',
                        'success' => 'delivered',
                        'danger' => 'canceled',
                    ]),
                TextColumn::make('project.title')
                    ->label(__('site.project'))
                    ->searchable(),
                TextColumn::make('user.nickname')
                    ->label(__('site.user'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => Jalalian::fromDateTime($state)->format('Y/m/d H:i:s')),
            ]);
    }
}