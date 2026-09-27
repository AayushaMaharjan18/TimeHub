<?php

namespace App\Filament\Resources\FooterSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class FooterSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('General')
                            ->schema([
                                Section::make('Brand')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('brand_name')->required()->maxLength(255),
                                        Toggle::make('is_active')->label('Active')->default(true)->inline(false),
                                        Textarea::make('brand_description')->rows(2)->columnSpanFull(),
                                        TextInput::make('copyright_text')->maxLength(255)->columnSpanFull(),
                                    ]),
                                Section::make('Social Media')
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('facebook_url')->label('Facebook URL')->url()->maxLength(255),
                                        TextInput::make('instagram_url')->label('Instagram URL')->url()->maxLength(255),
                                        TextInput::make('twitter_url')->label('Twitter / X URL')->url()->maxLength(255),
                                    ]),
                            ]),

                        Tab::make('Contact')
                            ->schema([
                                Section::make('Contact details')
                                    ->description('Shown in the footer and on the Contact page.')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('phone')->maxLength(255),
                                        TextInput::make('email')->email()->maxLength(255),
                                        TextInput::make('address')->maxLength(255),
                                        TextInput::make('whatsapp_number')
                                            ->label('WhatsApp Number')
                                            ->tel()
                                            ->maxLength(255)
                                            ->helperText('Full international format, e.g. +977-98XXXXXXXX. Powers the WhatsApp buttons.'),
                                        Textarea::make('business_hours')
                                            ->rows(3)
                                            ->placeholder("Sunday - Friday: 10:00 AM - 8:00 PM\nSaturday: 10:00 AM - 6:00 PM")
                                            ->helperText('One line per row.'),
                                        Textarea::make('map_embed_url')
                                            ->label('Google Maps embed URL')
                                            ->rows(3)
                                            ->helperText('Google Maps → Share → Embed a map → copy the src="…" URL only.'),
                                    ]),
                            ]),

                        Tab::make('Footer Links')
                            ->columns(2)
                            ->schema([
                                self::linkRepeater('quick_links', 'Quick Links', 'Add quick link'),
                                self::linkRepeater('customer_service_links', 'Customer Service Links', 'Add customer service link'),
                                Repeater::make('payment_methods')
                                    ->label('Payment methods shown in footer')
                                    ->schema([TextInput::make('name')->required()->maxLength(255)])
                                    ->addActionLabel('Add payment method')
                                    ->reorderable()
                                    ->grid(3)
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Homepage')
                            ->schema([
                                Section::make('Newsletter block')
                                    ->schema([
                                        TextInput::make('newsletter_title')->placeholder('Join Our Newsletter')->maxLength(255),
                                        Textarea::make('newsletter_text')->rows(2),
                                    ]),
                            ]),

                        Tab::make('About Page')
                            ->schema([
                                Section::make('Story')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('about_title')->placeholder('Our Story')->maxLength(255)->columnSpanFull(),
                                        RichEditor::make('about_content')
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('about'),
                                        FileUpload::make('about_image')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('about'),
                                    ]),
                                Repeater::make('about_values')
                                    ->label('Our Values')
                                    ->schema([
                                        TextInput::make('title')->required()->maxLength(255),
                                        Textarea::make('description')->rows(2),
                                    ])
                                    ->grid(3)
                                    ->reorderable()
                                    ->addActionLabel('Add value'),
                                Repeater::make('about_stats')
                                    ->label('Stats band')
                                    ->schema([
                                        TextInput::make('value')->placeholder('10,000+')->required()->maxLength(50),
                                        TextInput::make('label')->placeholder('Happy Customers')->required()->maxLength(255),
                                    ])
                                    ->columns(2)
                                    ->grid(2)
                                    ->reorderable()
                                    ->addActionLabel('Add stat'),
                                Repeater::make('about_team')
                                    ->label('Team')
                                    ->schema([
                                        TextInput::make('name')->required()->maxLength(255),
                                        TextInput::make('role')->maxLength(255),
                                        FileUpload::make('photo')->image()->imageEditor()->disk('public')->directory('team'),
                                    ])
                                    ->grid(3)
                                    ->reorderable()
                                    ->addActionLabel('Add team member'),
                            ]),
                    ]),
            ]);
    }

    private static function linkRepeater(string $name, string $label, string $addLabel): Repeater
    {
        return Repeater::make($name)
            ->label($label)
            ->schema([
                TextInput::make('label')->required()->maxLength(255),
                TextInput::make('url')->required()->maxLength(255)->placeholder('/shop'),
            ])
            ->columns(2)
            ->reorderable()
            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
            ->addActionLabel($addLabel);
    }
}
