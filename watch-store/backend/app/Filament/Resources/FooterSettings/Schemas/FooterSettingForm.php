<?php

namespace App\Filament\Resources\FooterSettings\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FooterSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Brand Section')
                    ->collapsible()
                    ->collapsed()
                    ->description('Configure your brand information displayed in the footer')
                    ->columns(2)
                    ->schema([
                        TextInput::make('brand_name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('brand_description')
                            ->columnSpanFull()
                            ->rows(2),
                    ]),

                Section::make('Social Media Links')
                    ->collapsible()
                    ->collapsed()
                    ->description('Enter your social media profile URLs')
                    ->columns(2)
                    ->schema([
                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('twitter_url')
                            ->label('Twitter / X URL')
                            ->url()
                            ->maxLength(255),
                    ]),

                Section::make('Quick Links')
                    ->collapsible()
                    ->collapsed()
                    ->description('Manage the quick links shown in the footer')
                    ->schema([
                        Repeater::make('quick_links')
                            ->label('')
                            ->simple(
                                TextInput::make('label')
                                    ->required()
                                    ->maxLength(255)
                            )
                            ->addActionLabel('Add Quick Link')
                            ->reorderable()
                            ->collapsible()
                            ->collapsed(),
                    ]),

                Section::make('Customer Service Links')
                    ->collapsible()
                    ->collapsed()
                    ->description('Manage the customer service links shown in the footer')
                    ->schema([
                        Repeater::make('customer_service_links')
                            ->label('')
                            ->simple(
                                TextInput::make('label')
                                    ->required()
                                    ->maxLength(255)
                            )
                            ->addActionLabel('Add Customer Service Link')
                            ->reorderable()
                            ->collapsible()
                            ->collapsed(),
                    ]),

                Section::make('Contact Information')
                    ->collapsible()
                    ->collapsed()
                    ->description('Your business contact details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('address')
                            ->maxLength(255),
                        TextInput::make('whatsapp_number')
                            ->label('WhatsApp Number')
                            ->tel()
                            ->maxLength(255)
                            ->helperText('Full international format, e.g. +977-98XXXXXXXX. Powers the WhatsApp contact/order-support buttons on the storefront.'),
                    ]),

                Section::make('Additional Settings')
                    ->collapsible()
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextInput::make('copyright_text')
                            ->label('Copyright Text')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Repeater::make('payment_methods')
                            ->label('Payment Methods')
                            ->simple(
                                TextInput::make('name')
                                    ->label('Payment Method')
                                    ->required()
                                    ->maxLength(255)
                            )
                            ->addActionLabel('Add Payment Method')
                            ->reorderable()
                            ->collapsible()
                            ->collapsed(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }
}