<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use Domain\Project\Models\Project;
use Domain\User\Models\User;
use Domain\Address\Models\Country;
use Domain\Address\Models\Province;
use Domain\Address\Models\City;
use Domain\Project\Models\ProjectCategory;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Morilog\Jalali\Jalalian;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\BadgeColumn;
use Domain\Claim\Models\Claim;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Project Management';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('site.projects');
    }

    public static function getModelLabel(): string
    {
        return __('site.project');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.projects');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.Project Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('site.project_information'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('site.title'))
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label(__('site.type'))
                            ->options([
                                Project::PASSENGER => __('site.passenger'),
                                Project::SENDER => __('site.sender'),
                            ])
                            ->required(),
                        TextInput::make('path_type')
                            ->label(__('site.path_type'))
                            ->maxLength(255),
                        TextInput::make('amount')
                            ->label(__('site.amount'))
                            ->numeric()
                            ->prefix(__('site.currency')),
                        TextInput::make('weight')
                            ->label(__('site.weight'))
                            ->numeric()
                            ->suffix(__('site.kilogram')),
                        TextInput::make('dimensions')
                            ->label(__('site.dimensions'))
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('site.description'))
                            ->rows(3)
                            ->maxLength(1000),
                        Toggle::make('vip')
                            ->label(__('site.vip'))
                            ->default(false),
                        TextInput::make('priority')
                            ->label(__('site.priority'))
                            ->numeric()
                            ->default(0),
                        Toggle::make('active')
                            ->label(__('site.active'))
                            ->default(true),
                    ])->columns(2),

                Section::make(__('site.location_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('o_country_id')
                                    ->label(__('site.origin_country'))
                                    ->options(Country::pluck('title', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('o_province_id', null);
                                        $set('o_city_id', null);
                                    }),
                                Select::make('d_country_id')
                                    ->label(__('site.destination_country'))
                                    ->options(Country::pluck('title', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('d_province_id', null);
                                        $set('d_city_id', null);
                                    }),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('o_province_id')
                                    ->label(__('site.origin_province'))
                                    ->options(function (callable $get) {
                                        $countryId = $get('o_country_id');
                                        if (!$countryId) return [];
                                        return Province::where('country_id', $countryId)->pluck('title', 'id');
                                    })
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('o_city_id', null);
                                    }),
                                Select::make('d_province_id')
                                    ->label(__('site.destination_province'))
                                    ->options(function (callable $get) {
                                        $countryId = $get('d_country_id');
                                        if (!$countryId) return [];
                                        return Province::where('country_id', $countryId)->pluck('title', 'id');
                                    })
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('d_city_id', null);
                                    }),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('o_city_id')
                                    ->label(__('site.origin_city'))
                                    ->options(function (callable $get) {
                                        $provinceId = $get('o_province_id');
                                        if (!$provinceId) return [];
                                        return City::where('province_id', $provinceId)->pluck('title', 'id');
                                    })
                                    ->searchable(),
                                Select::make('d_city_id')
                                    ->label(__('site.destination_city'))
                                    ->options(function (callable $get) {
                                        $provinceId = $get('d_province_id');
                                        if (!$provinceId) return [];
                                        return City::where('province_id', $provinceId)->pluck('title', 'id');
                                    })
                                    ->searchable(),
                            ]),
                        Textarea::make('address')
                            ->label(__('site.address'))
                            ->rows(2)
                            ->maxLength(500),
                    ]),

                Section::make(__('site.project_details'))
                    ->schema([
                        DatePicker::make('send_date')
                            ->label(__('site.send_date'))
                            ->displayFormat('Y/m/d'),
                        DatePicker::make('receive_date')
                            ->label(__('site.receive_date'))
                            ->displayFormat('Y/m/d'),
                        Select::make('status')
                            ->label(__('site.status'))
                            ->options([
                                Project::PENDING => __('site.pending'),
                                Project::APPROVED => __('site.approved'),
                                Project::INPROGRESS => __('site.in_progress'),
                                Project::COMPLETED => __('site.completed'),
                                Project::CANCELLED => __('site.canceled'),
                                Project::REJECT => __('site.reject'),
                                Project::FAILED => __('site.failed'),
                            ])
                            ->default(Project::PENDING)
                            ->required(),
                        Select::make('user_id')
                            ->label(__('site.project_user'))
                            ->options(User::pluck('nickname', 'id'))
                            ->searchable()
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit')
                            ->helperText(fn (string $context): string => $context === 'edit' ? __('site.user_cannot_be_changed') : ''),
                        Select::make('categories')
                            ->label(__('site.project_categories'))
                            ->multiple()
                            ->options(ProjectCategory::pluck('title', 'id'))
                            ->searchable(),
                        FileUpload::make('image')
                            ->label(__('site.project_image'))
                            ->placeholder(__('site.upload_project_image'))
                            ->image()
                            ->imageEditor()
                            ->disk('s3')
                            ->directory('/projects/images')
                    ])->columns(2),
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
                ImageColumn::make('image')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('site.image'))
                    ->url(fn ($record) => $record->image ? $record->image : null)
                    ->circular()
                    ->size(40),
                TextColumn::make('title')
                    ->label(__('site.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('type')
                    ->label(__('site.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Project::PASSENGER => 'primary',
                        Project::SENDER => 'secondary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Project::PASSENGER => __('site.passenger'),
                        Project::SENDER => __('site.sender'),
                        default => $state,
                    }),
                TextColumn::make('status')
                    ->label(__('site.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Project::PENDING => 'warning',
                        Project::APPROVED => 'success',
                        Project::INPROGRESS => 'info',
                        Project::COMPLETED => 'primary',
                        Project::CANCELLED => 'danger',
                        Project::REJECT => 'secondary',
                        Project::FAILED => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Project::PENDING => __('site.pending'),
                        Project::APPROVED => __('site.approved'),
                        Project::INPROGRESS => __('site.in_progress'),
                        Project::COMPLETED => __('site.completed'),
                        Project::CANCELLED => __('site.canceled'),
                        Project::REJECT => __('site.reject'),
                        Project::FAILED => __('site.failed'),
                        default => $state,
                    }),
                TextColumn::make('amount')
                    ->label(__('site.amount'))
                    ->money('IRR')
                    ->sortable(),
                TextColumn::make('weight')
                    ->label(__('site.weight'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->suffix(__('site.kilogram'))
                    ->sortable(),
                IconColumn::make('vip')
                    ->label(__('site.vip'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->falseColor('gray'),
                TextColumn::make('user.nickname')
                    ->label(__('site.project_user'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->url(fn ($record) => $record->user ? route('filament.admin.resources.users.edit', $record->user) : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->icon('heroicon-o-user')
                    ->tooltip(__('site.click_to_view_user_details')),
                TextColumn::make('claims_count')
                    ->label(__('site.claims'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->counts('claims')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->tooltip(__('site.total_claims_for_project')),
                TextColumn::make('claims_status')
                    ->label(__('site.claims_status'))
                    ->formatStateUsing(function ($record) {
                        $claims = $record->claims;
                        if ($claims->isEmpty()) {
                            return __('site.no_claims');
                        }

                        $statusCounts = $claims->groupBy('status')->map->count();
                        $statusLabels = [
                            Claim::PENDING => __('site.pending'),
                            Claim::APPROVED => __('site.approved'),
                            Claim::PAID => __('site.paid'),
                            Claim::INPROGRESS => __('site.in_progress'),
                            Claim::DELIVERED => __('site.delivered'),
                            Claim::CANCELED => __('site.canceled'),
                        ];

                        $statusTexts = [];
                        foreach ($statusCounts as $status => $count) {
                            $label = $statusLabels[$status] ?? $status;
                            $statusTexts[] = "{$label}: {$count}";
                        }

                        return implode(', ', $statusTexts);
                    })
                    ->wrap()
                    ->limit(100)
                    ->tooltip(__('site.claims_status_breakdown')),
                TextColumn::make('send_date')
                    ->label(__('site.send_date'))
                    ->date('Y/m/d')
                    ->sortable(),
                TextColumn::make('receive_date')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('site.receive_date'))
                    ->date('Y/m/d')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('site.created_at'))
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('site.type'))
                    ->options([
                        Project::PASSENGER => __('site.passenger'),
                        Project::SENDER => __('site.sender'),
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options([
                        Project::PENDING => __('site.pending'),
                        Project::APPROVED => __('site.approved'),
                        Project::INPROGRESS => __('site.in_progress'),
                        Project::COMPLETED => __('site.completed'),
                        Project::CANCELLED => __('site.canceled'),
                        Project::REJECT => __('site.reject'),
                        Project::FAILED => __('site.failed'),
                    ]),
                Tables\Filters\TernaryFilter::make('vip')
                    ->label(__('site.vip')),
                Tables\Filters\TernaryFilter::make('active')
                    ->label(__('site.active')),
                Tables\Filters\SelectFilter::make('has_claims')
                    ->label(__('site.has_claims'))
                    ->options([
                        'yes' => __('site.has_claims'),
                        'no' => __('site.no_claims'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === 'yes') {
                            return $query->has('claims');
                        }
                        if ($data['value'] === 'no') {
                            return $query->doesntHave('claims');
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_claims')
                    ->label(__('site.view_claims'))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('info')
                    ->url(fn (Project $record): string => route('filament.admin.resources.projects.edit', $record) . '?activeTab=claims')
                    ->openUrlInNewTab()
                    ->visible(fn (Project $record): bool => $record->claims()->count() > 0),
                Tables\Actions\Action::make('approve')
                    ->label(__('site.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Project $record): bool => $record->status === Project::PENDING)
                    ->action(function (Project $record) {
                        try {
                            $repository = app(\Domain\Project\Repositories\Contracts\IProjectRepository::class);
                            $repository->approve($record);

                            Notification::make()
                                ->title(__('site.project_approved_title'))
                                ->body(__('site.project_approved_content', ['project_title' => $record->title]))
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('site.error'))
                                ->body(__('site.something_went_wrong'))
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading(__('site.confirm_approve_project'))
                    ->modalDescription(__('site.confirm_approve_project_description'))
                    ->modalSubmitActionLabel(__('site.approve'))
                    ->modalCancelActionLabel(__('site.cancel')),
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make()
                    // ->before(function (Project $record) {
                    //     Notification::make()
                    //         ->title(__('site.confirm_delete_project'))
                    //         ->body(__('site.confirm_delete_project_description'))
                    //         ->warning()
                    //         ->send();
                    // })
                    // ->after(function (Project $record) {
                    //     Notification::make()
                    //         ->title(__('site.project_deleted_successfully'))
                    //         ->success()
                    //         ->send();
                    // }),
            ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make()
            //             ->label(__('site.delete_selected_projects'))
            //             ->after(function () {
            //                 Notification::make()
            //                     ->title(__('site.project_deleted_successfully'))
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
            \App\Filament\Resources\ProjectResource\RelationManagers\ClaimsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}