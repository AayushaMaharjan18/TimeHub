<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('price')
                    ->money('NPR')
                    ->sortable(),
                TextColumn::make('compare_price')
                    ->money('NPR')
                    ->sortable(),
                TextColumn::make('final_price')
                    ->money('NPR')
                    ->sortable(),
                TextColumn::make('discount_percentage')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('in_stock')
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->boolean(),
                IconColumn::make('is_new')
                    ->boolean(),
                IconColumn::make('is_best_seller')
                    ->boolean(),
                IconColumn::make('is_limited_edition')
                    ->boolean(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('gender')
                    ->searchable(),
                TextColumn::make('movement')
                    ->searchable(),
                TextColumn::make('strap')
                    ->searchable(),
                TextColumn::make('case_material')
                    ->searchable(),
                TextColumn::make('case_diameter')
                    ->searchable(),
                TextColumn::make('case_thickness')
                    ->searchable(),
                TextColumn::make('water_resistance')
                    ->searchable(),
                TextColumn::make('dial_color')
                    ->searchable(),
                TextColumn::make('glass_type')
                    ->searchable(),
                TextColumn::make('warranty_period')
                    ->searchable(),
                TextColumn::make('average_rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reviews_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('brand_id')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('thumbnail')
                    ->disk('public')
                    ->width(60)
                    ->height(60)
                    ->square(),
                TextColumn::make('meta_title')
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
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
