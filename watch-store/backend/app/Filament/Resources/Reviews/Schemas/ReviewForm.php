<?php

namespace App\Filament\Resources\Reviews\Schemas;

use App\Models\Review;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Display-only related info: Placeholder never binds to a model
                // attribute or takes part in the save payload, unlike a disabled
                // TextInput on a dot-notation path (which left a stray empty
                // "product"/"user"/"order" array in the saved state and broke
                // the update with a SQL error — see ReviewAdminEditTest).
                Placeholder::make('product_display')
                    ->label('Product')
                    ->content(fn (?Review $record): string => $record?->product?->name ?? '—'),
                Placeholder::make('customer_display')
                    ->label('Customer')
                    ->content(fn (?Review $record): string => $record?->user?->name ?? '—'),
                Placeholder::make('customer_email_display')
                    ->label('Email')
                    ->content(fn (?Review $record): string => $record?->user?->email ?? '—'),
                Placeholder::make('order_display')
                    ->label('Order #')
                    ->content(fn (?Review $record): string => $record?->order?->order_number ?? '—'),
                Select::make('rating')->options([
                    1 => '1 - Poor',
                    2 => '2 - Fair',
                    3 => '3 - Good',
                    4 => '4 - Very Good',
                    5 => '5 - Excellent',
                ])->required(),
                TextInput::make('title')->maxLength(255),
                Textarea::make('comment')->required()->rows(4),
                Select::make('status')
                    ->options([
                        Review::STATUS_PENDING => 'Pending',
                        Review::STATUS_APPROVED => 'Approved',
                        Review::STATUS_REJECTED => 'Rejected',
                    ])
                    ->default(Review::STATUS_PENDING)
                    ->required(),
                Toggle::make('is_visible_on_homepage')->label('Show on homepage'),
                Toggle::make('is_verified_purchase')
                    ->label('Verified purchase')
                    ->disabled()
                    ->dehydrated(),
            ]);
    }
}

