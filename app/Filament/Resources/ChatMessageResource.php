<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatMessageResource\Pages;
use Domain\Chat\Models\ChatMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Morilog\Jalali\Jalalian;

class ChatMessageResource extends Resource
{
    protected static ?string $model = ChatMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';

    protected static ?string $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 22;



    public static function getNavigationLabel(): string
    {
        return __('site.chat_messages');
    }

    public static function getModelLabel(): string
    {
        return __('site.chat_message');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.chat_messages');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.Communication');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('site.message_information'))
                    ->schema([
                        Forms\Components\Select::make('chat_id')
                            ->label(__('site.chat'))
                            ->relationship('chat', 'id')
                            ->searchable()
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->label(__('site.user'))
                            ->relationship('user', 'nickname')
                            ->searchable()
                            ->disabled(),
                        Forms\Components\Textarea::make('message')
                            ->label(__('site.message'))
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('status')
                            ->label(__('site.status'))
                            ->options([
                                'pending' => __('site.pending'),
                                'read' => __('site.read'),
                            ])
                            ->disabled(),
                        Forms\Components\TextInput::make('file')
                            ->label(__('site.file'))
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
                TextColumn::make('chat.id')
                    ->label(__('site.chat_id'))
                    ->sortable()
                    ->searchable()
                    ->url(fn ($record) => $record->chat ? route('filament.admin.resources.chats.view', $record->chat) : null)
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn ($record) => $record->chat ? __('site.All messages') : 'N/A'),
                TextColumn::make('user.nickname')
                    ->label(__('site.user'))
                    ->sortable()
                    ->searchable()
                    ->url(fn ($record) => $record->user ? route('filament.admin.resources.users.view', $record->user) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('message')
                    ->label(__('site.message'))
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('status')
                    ->label(__('site.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->getStateUsing(fn ($record) => $record->status === 'read'),
                TextColumn::make('file')
                    ->label(__('site.file'))
                    ->limit(30)
                    ->searchable()
                    ->toggleable()
                    ->url(fn ($record) => $record->file ? url('storage/s3/' . urlencode($record->file)) : null)
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn ($record) => $record->file ? __('site.download file') : __('site.no_file')),
                TextColumn::make('created_at')
                    ->label(__('site.created_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => Jalalian::fromDateTime($state)->format('Y/m/d H:i:s')),
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
                        'pending' => __('site.pending'),
                        'read' => __('site.read'),
                    ]),
                Tables\Filters\SelectFilter::make('chat_id')
                    ->label(__('site.chat'))
                    ->relationship('chat', 'id')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('site.user'))
                    ->relationship('user', 'nickname')
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('has_file')
                    ->label(__('site.has_file'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('file'),
                        false: fn (Builder $query) => $query->whereNull('file'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListChatMessages::route('/'),
            'view' => Pages\ViewChatMessage::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNotNull('file')->count();
    }
}
