<?php

namespace App\Filament\Resources\PlatformNotifications\Tables;

use App\Models\PlatformNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PlatformNotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(45),

                TextColumn::make('feature_key')
                    ->label('Fonctionnalite')
                    ->formatStateUsing(fn (?string $state): string => PlatformNotification::featureOptions()[$state] ?? (string) $state)
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state): string => PlatformNotification::typeOptions()[$state] ?? (string) $state)
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        PlatformNotification::TYPE_SUCCESS => 'success',
                        PlatformNotification::TYPE_WARNING => 'warning',
                        PlatformNotification::TYPE_DANGER => 'danger',
                        default => 'info',
                    })
                    ->sortable(),

                TextColumn::make('target_roles')
                    ->label('Destinataires')
                    ->formatStateUsing(function ($state): string {
                        $roles = is_array($state) ? $state : [];

                        return collect($roles)
                            ->map(fn (string $role): string => PlatformNotification::roleOptions()[$role] ?? $role)
                            ->join(', ');
                    })
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn (?string $state): string => PlatformNotification::statusOptions()[$state] ?? (string) $state)
                    ->badge()
                    ->color(fn (?string $state): string => $state === PlatformNotification::STATUS_SENT ? 'success' : 'gray')
                    ->sortable(),

                TextColumn::make('sent_count')
                    ->label('Envoyees')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('sent_at')
                    ->label('Date envoi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Cree par')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(PlatformNotification::statusOptions()),
                SelectFilter::make('feature_key')
                    ->label('Fonctionnalite')
                    ->options(PlatformNotification::featureOptions()),
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(PlatformNotification::typeOptions()),
            ])
            ->recordActions([
                Action::make('send')
                    ->label('Envoyer')
                    ->icon('heroicon-m-paper-airplane')
                    ->color('primary')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Envoyer cette notification ?')
                    ->modalDescription('Les utilisateurs des roles selectionnes recevront cette notification dans la cloche Filament.')
                    ->visible(fn (PlatformNotification $record): bool => $record->sent_at === null)
                    ->action(function (PlatformNotification $record): void {
                        $count = $record->sendToRecipients();

                        if ($count === 0) {
                            Notification::make()
                                ->title('Aucun destinataire trouve')
                                ->body('Verifiez les roles selectionnes et les comptes actifs.')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Notification envoyee')
                            ->body("{$count} utilisateur(s) notifie(s).")
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->label('Modifier')
                    ->button(),

                DeleteAction::make()
                    ->label('Supprimer')
                    ->button()
                    ->color('danger'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
