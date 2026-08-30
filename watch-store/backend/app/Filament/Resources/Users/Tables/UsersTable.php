<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('role')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, User $record) {
                        if ($record->id === auth()->id()) {
                            Notification::make()
                                ->title('You cannot delete your own account.')
                                ->danger()
                                ->send();
                            $action->cancel();
                            return;
                        }

                        if ($record->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
                            Notification::make()
                                ->title('Cannot delete the last admin account.')
                                ->body('Create another admin user before removing this one.')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, \Illuminate\Support\Collection $records) {
                            $deletingAllAdmins = $records->where('role', 'admin')->count() >= User::where('role', 'admin')->count();
                            $deletingSelf = $records->contains('id', auth()->id());

                            if ($deletingSelf || ($deletingAllAdmins && User::where('role', 'admin')->count() > 0)) {
                                Notification::make()
                                    ->title($deletingSelf ? 'You cannot delete your own account.' : 'Cannot delete every admin account.')
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }
}
