<?php

namespace App\Filament\Resources\Offers;

use App\Filament\Resources\Offers\Pages\ManageOffers;
use App\Models\Offer;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class OfferResource extends Resource
{
    protected static ?string $model = Offer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|\UnitEnum|null $navigationGroup = 'Homepage';

    protected static ?string $navigationLabel = 'Promo Banners';

    protected static ?string $modelLabel = 'Promo Banner';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('label')->placeholder('Special Offer')->maxLength(255),
                TextInput::make('title')->placeholder('Luxury Watches at 30% Off')->required()->maxLength(255),
                Textarea::make('description')->rows(2)->columnSpanFull(),
                TextInput::make('button_text')->placeholder('Shop the Sale')->maxLength(255),
                TextInput::make('button_url')->placeholder('/shop?sort=price_low')->maxLength(255),
                FileUpload::make('image')
                    ->label('Background image (optional)')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('offers')
                    ->columnSpanFull(),
                DateTimePicker::make('starts_at')->helperText('Leave empty to show immediately.'),
                DateTimePicker::make('ends_at')->helperText('Leave empty to never expire.'),
                TextInput::make('sort_order')->numeric()->default(0),
                Toggle::make('is_active')->label('Active')->default(true)->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')->disk('public')->width(100)->height(50),
                TextColumn::make('title')->searchable(),
                TextColumn::make('starts_at')->dateTime()->placeholder('—'),
                TextColumn::make('ends_at')->dateTime()->placeholder('—'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageOffers::route('/')];
    }
}
