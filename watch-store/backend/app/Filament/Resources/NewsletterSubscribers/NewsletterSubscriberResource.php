<?php

namespace App\Filament\Resources\NewsletterSubscribers;

use App\Filament\Resources\NewsletterSubscribers\Pages\ManageNewsletterSubscribers;
use App\Models\NewsletterSubscriber;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|\UnitEnum|null $navigationGroup = 'Customers';

    protected static ?string $navigationLabel = 'Newsletter Subscribers';

    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Toggle::make('is_active')->label('Subscribed')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')->searchable()->copyable(),
                ToggleColumn::make('is_active')->label('Subscribed'),
                TextColumn::make('created_at')->label('Subscribed at')->dateTime()->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterSubscribers::route('/')];
    }
}
