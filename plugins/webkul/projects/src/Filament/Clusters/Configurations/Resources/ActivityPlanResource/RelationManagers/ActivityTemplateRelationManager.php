<?php

namespace Webkul\Project\Filament\Clusters\Configurations\Resources\ActivityPlanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Webkul\Security\Models\User;
use Webkul\Support\Enums\ActivityDelayInterval;
use Webkul\Support\Enums\ActivityDelayUnit;
use Webkul\Support\Enums\ActivityResponsibleType;
use Webkul\Support\Filament\Resources\ActivityTypeResource;
use Webkul\Support\Models\ActivityType;

class ActivityTemplateRelationManager extends RelationManager
{
    protected static string $relationship = 'activityPlanTemplates';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.form.sections.activity-details.title'))
                                    ->schema([
                                        Forms\Components\Group::make()
                                            ->schema([
                                                Forms\Components\Select::make('activity_type_id')
                                                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.form.sections.activity-details.fields.activity-type'))
                                                    ->options(ActivityType::pluck('name', 'id'))
                                                    ->relationship('activityType', 'name')
                                                    ->searchable()
                                                    ->required()
                                                    ->default(ActivityType::first()?->id)
                                                    ->createOptionForm(fn (Form $form) => ActivityTypeResource::form($form))
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        $activityType = ActivityType::find($state);

                                                        if ($activityType && $activityType->default_user_id) {
                                                            $set('responsible_type', ActivityResponsibleType::OTHER->value);

                                                            $set('responsible_id', $activityType->default_user_id);
                                                        }
                                                    }),
                                                Forms\Components\TextInput::make('summary')
                                                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.form.sections.activity-details.fields.summary')),
                                            ])->columns(2),
                                        Forms\Components\RichEditor::make('note')
                                            ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.form.sections.activity-details.fields.note')),
                                    ]),
                            ])
                            ->columnSpan(['lg' => 2]),
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Section::make(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.form.sections.assignment.title'))
                                    ->schema([
                                        Forms\Components\Select::make('responsible_type')
                                            ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.form.sections.assignment.fields.assignment'))
                                            ->options(ActivityResponsibleType::options())
                                            ->default(ActivityResponsibleType::ON_DEMAND->value)
                                            ->required()
                                            ->searchable()
                                            ->live()
                                            ->required()
                                            ->preload(),
                                        Forms\Components\Select::make('responsible_id')
                                            ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.form.sections.assignment.fields.assignee'))
                                            ->options(fn () => User::pluck('name', 'id'))
                                            ->hidden(fn (Get $get) => $get('responsible_type') !== ActivityResponsibleType::OTHER->value)
                                            ->searchable()
                                            ->preload(),
                                    ]),
                                Forms\Components\Section::make(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.form.sections.delay-information.title'))
                                    ->schema([
                                        Forms\Components\TextInput::make('delay_count')
                                            ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.form.sections.delay-information.fields.delay-count'))
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue(99999999999),
                                        Forms\Components\Select::make('delay_unit')
                                            ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.form.sections.delay-information.fields.delay-unit'))
                                            ->searchable()
                                            ->preload()
                                            ->default(ActivityDelayUnit::DAYS->value)
                                            ->options(ActivityDelayUnit::options()),
                                        Forms\Components\Select::make('delay_from')
                                            ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.form.sections.delay-information.fields.delay-from'))
                                            ->searchable()
                                            ->preload()
                                            ->default(ActivityDelayInterval::BEFORE_PLAN_DATE->value)
                                            ->options(ActivityDelayInterval::options())
                                            ->helperText(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.form.sections.delay-information.fields.delay-from-helper-text')),
                                    ]),
                            ])
                            ->columnSpan(['lg' => 1]),
                    ])
                    ->columns(3),
            ])
            ->columns('full');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activityType.name')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.columns.activity-type'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('summary')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.columns.summary'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('responsible_type')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.columns.assignment'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('responsible.name')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.columns.assigned-to'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('delay_count')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.columns.interval'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('delay_unit')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.columns.delay-unit'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('delay_from')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.columns.delay-from'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.columns.created-by'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.columns.created-at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.columns.updated-at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('activity_type_id')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.filters.activity-type'))
                    ->options(ActivityType::pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.filters.activity-status')),
                Tables\Filters\Filter::make('has_delay')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.filters.has-delay'))
                    ->query(fn ($query) => $query->whereNotNull('delay_count')),
            ])
            ->groups([
                Tables\Grouping\Group::make('responsible.name')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.groups.activity-type'))
                    ->collapsible(),
                Tables\Grouping\Group::make('responsible_type')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.groups.assignment'))
                    ->collapsible(),
                Tables\Grouping\Group::make('created_at')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.groups.created-at'))
                    ->collapsible(),
                Tables\Grouping\Group::make('updated_at')
                    ->label(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.groups.updated-at'))
                    ->date()
                    ->collapsible(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalWidth(MaxWidth::FitContent)
                    ->mutateFormDataUsing(function (array $data): array {
                        return [
                            ...$data,
                            'creator_id' => Auth::user()->id,
                        ];
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Activity plan template created')
                            ->body('The activity plan template has been created successfully.'),
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->modalWidth(MaxWidth::FitContent)
                        ->mutateFormDataUsing(function (array $data): array {
                            return [
                                ...$data,
                                'creator_id' => Auth::user()->id,
                            ];
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.actions.edit.notification.title'))
                                ->body(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.actions.edit.notification.body')),
                        ),
                    Tables\Actions\DeleteAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.actions.delete.notification.title'))
                                ->body(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.actions.delete.notification.body')),
                        ),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.bulk-actions.delete.notification.title'))
                            ->body(__('projects::filament/clusters/configurations/resources/activity-plan/relation-managers/activity-template.table.bulk-actions.delete.notification.body')),
                    ),
            ])
            ->reorderable('sort');
    }
}
