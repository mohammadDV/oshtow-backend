<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectCategoryResource\Pages;
use Domain\Project\Models\ProjectCategory;
use Domain\User\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Auth;

class ProjectCategoryResource extends Resource
{
    protected static ?string $model = ProjectCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Project Management';

    public static function getNavigationLabel(): string
    {
        return __('site.project_categories');
    }

    public static function getModelLabel(): string
    {
        return __('site.project_category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.project_categories');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.Project Category Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('site.project_category_information'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('site.category_title'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('site.enter_category_title')),
                        Select::make('status')
                            ->label(__('site.category_status'))
                            ->options([
                                1 => __('site.Active'),
                                0 => __('site.Inactive'),
                            ])
                            ->default(1)
                            ->required(),
                        TextInput::make('user_id')
                            ->label(__('site.category_user'))
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder(fn ($record) => $record && $record->user ? $record->user->nickname : Auth::user()->nickname)
                            ->helperText(__('site.only_admin_users_allowed')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // If user is not level 3 (admin), only show their own categories
                if (Auth::user()->level !== 3) {
                    $query->where('user_id', Auth::id());
                }
            })
            ->columns([
                TextColumn::make('id')
                    ->label(__('site.table_id'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->label(__('site.category_title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('status')
                    ->label(__('site.category_status'))
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'success',
                        0 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => $state === 1 ? __('site.Active') : __('site.Inactive')),
                TextColumn::make('user.nickname')
                    ->label(__('site.category_user'))
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->user ? route('filament.admin.resources.users.edit', $record->user) : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->icon('heroicon-o-user')
                    ->tooltip(__('site.click_to_view_user_details')),
                // TextColumn::make('projects_count')
                //     ->label(__('site.category_projects'))
                //     ->counts('projects')
                //     ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.category_status'))
                    ->options([
                        1 => __('site.Active'),
                        0 => __('site.Inactive'),
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('site.category_user'))
                    ->options(User::where('level', 3)->pluck('nickname', 'id'))
                    ->visible(fn () => Auth::user()->level === 3),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make()
                //     ->before(function (ProjectCategory $record) {
                //         Notification::make()
                //             ->title(__('site.confirm_delete_project_category'))
                //             ->body(__('site.confirm_delete_project_category_description'))
                //             ->warning()
                //             ->send();
                //     })
                //     ->after(function (ProjectCategory $record) {
                //         Notification::make()
                //             ->title(__('site.project_category_deleted_successfully'))
                //             ->success()
                //             ->send();
                //     }),
            ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make()
            //             ->label(__('site.delete_selected_project_categories'))
            //             ->after(function () {
            //                 Notification::make()
            //                     ->title(__('site.project_category_deleted_successfully'))
            //                     ->success()
            //                     ->send();
            //             }),
            //     ]),
            // ])
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
            'index' => Pages\ListProjectCategories::route('/'),
            'create' => Pages\CreateProjectCategory::route('/create'),
            'view' => Pages\ViewProjectCategory::route('/{record}'),
            'edit' => Pages\EditProjectCategory::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
