<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Customers';

    protected static ?string $navigationLabel = 'Contact Messages';

    protected static ?string $recordTitleAttribute = 'subject';

    public static function getNavigationBadge(): ?string
    {
        $unread = ContactMessage::where('is_read', false)->count();

        return $unread > 0 ? (string) $unread : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextEntry::make('name'),
            TextEntry::make('email')->copyable(),
            TextEntry::make('phone')->placeholder('—'),
            TextEntry::make('subject')->placeholder('—'),
            TextEntry::make('created_at')->label('Received')->dateTime(),
            TextEntry::make('message')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('is_read')->label('Read')->boolean(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('subject')->placeholder('—'),
                TextColumn::make('message')->limit(60),
                TextColumn::make('created_at')->label('Received')->since()->sortable(),
            ])
            ->filters([TernaryFilter::make('is_read')->label('Read')])
            ->recordActions([
                ViewAction::make()->after(fn (ContactMessage $record) => $record->update(['is_read' => true])),
                Action::make('toggleRead')
                    ->label(fn (ContactMessage $record) => $record->is_read ? 'Mark unread' : 'Mark read')
                    ->icon(Heroicon::OutlinedCheck)
                    ->action(fn (ContactMessage $record) => $record->update(['is_read' => ! $record->is_read])),
                DeleteAction::make(),
            ])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageContactMessages::route('/')];
    }
}
