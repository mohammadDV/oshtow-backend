<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClaimResource\Pages;
use App\Filament\Resources\ClaimResource\RelationManagers\ClaimStepsRelationManager;
use App\Filament\Resources\ClaimResource\RelationManagers\PaymentSecuresRelationManager;
use Domain\Claim\Models\Claim;
use Domain\Project\Models\Project;
use Domain\User\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;

class ClaimResource extends Resource
{
    protected static ?string $model = Claim::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Project Management';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('site.claims');
    }

    public static function getModelLabel(): string
    {
        return __('site.claim');
    }

    public static function getPluralModelLabel(): string
    {
        return __('site.claims');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('site.Project Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('site.claim_information'))
                    ->schema([
                        Textarea::make('description')
                            ->label(__('site.description'))
                            ->required()
                            ->rows(3)
                            ->maxLength(1000),
                        TextInput::make('amount')
                            ->label(__('site.amount'))
                            ->numeric()
                            ->prefix(__('site.currency')),
                        TextInput::make('weight')
                            ->label(__('site.weight'))
                            ->numeric()
                            ->suffix(__('site.kilogram')),
                        Textarea::make('address')
                            ->label(__('site.address'))
                            ->rows(2)
                            ->maxLength(500),
                        Select::make('address_type')
                            ->label(__('site.address_type'))
                            ->options([
                                'me' => __('site.me'),
                                'other' => __('site.other'),
                            ])
                            ->default('me'),
                        Select::make('status')
                            ->label(__('site.status'))
                            ->options([
                                Claim::PENDING => __('site.pending'),
                                Claim::APPROVED => __('site.approved'),
                                Claim::PAID => __('site.paid'),
                                Claim::INPROGRESS => __('site.in_progress'),
                                Claim::DELIVERED => __('site.delivered'),
                                Claim::CANCELED => __('site.canceled'),
                            ])
                            ->default(Claim::PENDING)
                            ->required(),
                    ])->columns(2),

                Section::make(__('site.relationships'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('project_id')
                                    ->label(__('site.project'))
                                    ->options(Project::pluck('title', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (callable $set) {
                                        $set('sponsor_id', null);
                                    }),
                                Select::make('user_id')
                                    ->label(__('site.claim_user'))
                                    ->options(User::pluck('nickname', 'id'))
                                    ->searchable()
                                    ->required(),
                            ]),
                        Select::make('sponsor_id')
                            ->label(__('site.sponsor'))
                            ->options(function (callable $get) {
                                $projectId = $get('project_id');
                                if (!$projectId) return [];

                                $project = Project::find($projectId);
                                if (!$project) return [];

                                return User::whereIn('id', [$project->user_id, $get('user_id')])
                                    ->pluck('nickname', 'id');
                            })
                            ->searchable()
                            ->required(),
                    ]),

                Section::make(__('site.documents'))
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('site.image'))
                            ->placeholder(__('site.upload_claim_image'))
                            ->image()
                            ->imageEditor()
                            ->disk('s3')
                            ->directory('/claims/images'),
                        FileUpload::make('confirmation_image')
                            ->label(__('site.confirmation_image'))
                            ->placeholder(__('site.upload_confirmation_image'))
                            ->image()
                            ->imageEditor()
                            ->disk('s3')
                            ->directory('/claims/confirmations'),
                        Textarea::make('confirmation_description')
                            ->label(__('site.confirmation_description'))
                            ->rows(3)
                            ->maxLength(500),
                    ])->columns(2),

                Section::make(__('site.codes'))
                    ->schema([
                        TextInput::make('delivery_code')
                            ->label(__('site.delivery_code'))
                            ->maxLength(50),
                        TextInput::make('confirmed_code')
                            ->label(__('site.confirmed_code'))
                            ->maxLength(50),
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
                    ->label(__('site.image'))
                    ->url(fn ($record) => $record->image ? $record->image : null)
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->size(40),
                TextColumn::make('description')
                    ->label(__('site.description'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('amount')
                    ->label(__('site.amount'))
                    ->money('IRR')
                    ->sortable(),
                TextColumn::make('weight')
                    ->label(__('site.weight'))
                    ->suffix(__('site.kilogram'))
                    ->toggleable(isToggledHiddenByDefault: true)
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
                TextColumn::make('project.title')
                    ->label(__('site.project'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->url(fn ($record) => $record->project ? route('filament.admin.resources.projects.view', $record->project) : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->icon('heroicon-o-rectangle-stack')
                    ->tooltip(__('site.click_to_view_project_details')),
                TextColumn::make('user.nickname')
                    ->label(__('site.claim_user'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->url(fn ($record) => $record->user ? route('filament.admin.resources.users.view', $record->user) : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->icon('heroicon-o-user')
                    ->tooltip(__('site.click_to_view_user_details')),
                TextColumn::make('sponsor.nickname')
                    ->label(__('site.sponsor'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->url(fn ($record) => $record->sponsor ? route('filament.admin.resources.users.view', $record->sponsor) : null)
                    ->openUrlInNewTab()
                    ->color('secondary')
                    ->icon('heroicon-o-user-group')
                    ->tooltip(__('site.click_to_view_sponsor_details')),
                TextColumn::make('claim_steps_count')
                    ->label(__('site.claim_steps'))
                    ->counts('claimSteps')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-list-bullet')
                    ->tooltip(__('site.total_steps_for_claim')),
                TextColumn::make('payment_secures_count')
                    ->label(__('site.payment_secures'))
                    ->counts('paymentSecures')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-o-shield-check')
                    ->tooltip(__('site.total_payment_secures_for_claim')),
                TextColumn::make('created_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('site.created_at'))
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('site.status'))
                    ->options([
                        Claim::PENDING => __('site.pending'),
                        Claim::APPROVED => __('site.approved'),
                        Claim::PAID => __('site.paid'),
                        Claim::INPROGRESS => __('site.in_progress'),
                        Claim::DELIVERED => __('site.delivered'),
                        Claim::CANCELED => __('site.canceled'),
                    ]),
                SelectFilter::make('project_id')
                    ->label(__('site.project'))
                    ->options(Project::pluck('title', 'id'))
                    ->searchable(),
                SelectFilter::make('user_id')
                    ->label(__('site.claim_user'))
                    ->options(User::pluck('nickname', 'id'))
                    ->searchable(),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('site.view_claim')),
                Tables\Actions\EditAction::make()
                    ->label(__('site.edit_claim')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('site.delete_selected_claims')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ClaimStepsRelationManager::class,
            PaymentSecuresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClaims::route('/'),
            'create' => Pages\CreateClaim::route('/create'),
            'view' => Pages\ViewClaim::route('/{record}'),
            'edit' => Pages\EditClaim::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}