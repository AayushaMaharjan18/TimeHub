<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable(),
                TextColumn::make('shipping_name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('total')
                    ->money('NPR')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'gray' => Order::STATUS_PENDING,
                        'info' => [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING, Order::STATUS_PACKED],
                        'warning' => [Order::STATUS_SHIPPED, Order::STATUS_OUT_FOR_DELIVERY, Order::STATUS_RETURN_REQUESTED],
                        'success' => Order::STATUS_DELIVERED,
                        'danger' => [Order::STATUS_CANCELLED, Order::STATUS_RETURNED, Order::STATUS_REFUNDED],
                    ]),
                BadgeColumn::make('payment_status')
                    ->colors([
                        'danger' => ['unpaid', 'failed'],
                        'warning' => ['pending', 'initiated'],
                        'success' => 'paid',
                        'gray' => 'refunded',
                    ]),
                TextColumn::make('payment_method')
                    ->badge(),
                TextColumn::make('tracking_number')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('shipping_district')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    Order::STATUS_PENDING => 'Pending',
                    Order::STATUS_CONFIRMED => 'Confirmed',
                    Order::STATUS_PROCESSING => 'Processing',
                    Order::STATUS_PACKED => 'Packed',
                    Order::STATUS_SHIPPED => 'Shipped',
                    Order::STATUS_OUT_FOR_DELIVERY => 'Out for Delivery',
                    Order::STATUS_DELIVERED => 'Delivered',
                    Order::STATUS_CANCELLED => 'Cancelled',
                    Order::STATUS_RETURN_REQUESTED => 'Return Requested',
                    Order::STATUS_RETURNED => 'Returned',
                    Order::STATUS_REFUNDED => 'Refunded',
                ]),
                SelectFilter::make('payment_status')->options([
                    'unpaid' => 'Unpaid',
                    'pending' => 'Pending',
                    'initiated' => 'Initiated',
                    'paid' => 'Paid',
                    'failed' => 'Failed',
                    'refunded' => 'Refunded',
                ]),
                SelectFilter::make('payment_method')->options([
                    'cod' => 'Cash on Delivery',
                    'esewa' => 'eSewa',
                    'khalti' => 'Khalti',
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
