<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    private const STATUS_OPTIONS = [
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
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required()
                    ->disabledOn('edit'),
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                TextInput::make('shipping_cost')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rs.'),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->required()
                    ->default(Order::STATUS_PENDING)
                    ->options(self::STATUS_OPTIONS)
                    ->helperText('Changing this is logged to the order\'s status history and (when WhatsApp is configured) notifies the customer. A delivered/cancelled/returned/refunded order cannot be moved to a different status.'),
                Select::make('payment_method')
                    ->required()
                    ->default('cod')
                    ->options([
                        'cod' => 'Cash on Delivery',
                        'esewa' => 'eSewa',
                        'khalti' => 'Khalti',
                    ]),
                Select::make('payment_status')
                    ->required()
                    ->default(Order::PAYMENT_STATUS_UNPAID)
                    ->options([
                        Order::PAYMENT_STATUS_UNPAID => 'Unpaid',
                        Order::PAYMENT_STATUS_PENDING => 'Pending',
                        Order::PAYMENT_STATUS_INITIATED => 'Initiated',
                        Order::PAYMENT_STATUS_PAID => 'Paid',
                        Order::PAYMENT_STATUS_FAILED => 'Failed',
                        Order::PAYMENT_STATUS_REFUNDED => 'Refunded',
                    ]),
                TextInput::make('tracking_number')
                    ->maxLength(255),
                TextInput::make('courier_name')
                    ->maxLength(255),
                TextInput::make('shipping_name')
                    ->required(),
                TextInput::make('shipping_phone')
                    ->tel()
                    ->required(),
                TextInput::make('shipping_district')
                    ->required(),
                TextInput::make('shipping_municipality')
                    ->required(),
                TextInput::make('shipping_ward')
                    ->required(),
                TextInput::make('shipping_street')
                    ->required(),
                DatePicker::make('estimated_delivery_date'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
