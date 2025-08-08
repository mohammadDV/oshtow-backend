<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatResource\Pages;
use App\Filament\Resources\ChatResource\RelationManagers;
use Domain\Chat\Models\Chat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Morilog\Jalali\Jalalian;

class ChatResource extends Resource
{
    protected static ?string $model = Chat::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Communication';

    public static function getNavigationLabel(): string
    {
        return __('site.chats');
    }

    public static function getModelLabel(): string
    {
        return __('site.chat');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.chats');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.Communication');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('site.chat_information'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('site.user'))
                            ->relationship('user', 'nickname')
                            ->searchable()
                            ->disabled(),
                        Forms\Components\Select::make('target_id')
                            ->label(__('site.target_user'))
                            ->relationship('target', 'nickname')
                            ->searchable()
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->label(__('site.status'))
                            ->options([
                                'active' => __('site.active'),
                                'closed' => __('site.closed'),
                            ])
                            ->disabled(),
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
                    ->sortable()
                    ->searchable()
                    ->url(fn ($record) => $record->user ? route('filament.admin.resources.users.view', $record->user) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('target.nickname')
                    ->label(__('site.target_user'))
                    ->sortable()
                    ->searchable()
                    ->url(fn ($record) => $record->target ? route('filament.admin.resources.users.view', $record->target) : null)
                    ->openUrlInNewTab(),
                IconColumn::make('status')
                    ->label(__('site.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => $record->status === 'active'),
                TextColumn::make('messages_count')
                    ->label(__('site.messages_count'))
                    ->counts('messages')
                    ->sortable(),
                TextColumn::make('lastMessage.message')
                    ->label(__('site.last_message'))
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('lastMessage.created_at')
                    ->label(__('site.last_message_time'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? Jalalian::fromDateTime($state)->format('Y/m/d H:i:s') : 'N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        'active' => __('site.active'),
                        'closed' => __('site.closed'),
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('site.user'))
                    ->relationship('user', 'nickname')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('target_id')
                    ->label(__('site.target_user'))
                    ->relationship('target', 'nickname')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChats::route('/'),
            'view' => Pages\ViewChat::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
