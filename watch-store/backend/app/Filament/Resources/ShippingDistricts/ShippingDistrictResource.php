<?php

namespace App\Filament\Resources\ShippingDistricts;

use App\Filament\Resources\ShippingDistricts\Pages\ManageShippingDistricts;
use App\Models\ShippingDistrict;
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

class ShippingDistrictResource extends Resource
{
    protected static ?string $model = ShippingDistrict::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static \UnitEnum|string|null $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Shipping Rates';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('District')->required()->unique(ignoreRecord: true)->maxLength(255),
            TextInput::make('cost')->label('Shipping cost')->numeric()->prefix('Rs.')->minValue(0)->required(),
            TextInput::make('delivery_days')->placeholder('2-3 days')->maxLength(255),
            Toggle::make('is_active')->label('Available at checkout')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->label('District')->searchable()->sortable(),
                TextColumn::make('cost')->money('NPR')->sortable(),
                TextColumn::make('delivery_days'),
                ToggleColumn::make('is_active')->label('Available'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageShippingDistricts::route('/')];
    }
}
