<?php

namespace App\Filament\Resources\HeroSliders;

use App\Filament\Resources\HeroSliders\Pages\ManageHeroSliders;
use App\Models\HeroSlider;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

class HeroSliderResource extends Resource
{
    protected static ?string $model = HeroSlider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Homepage';

    protected static ?string $navigationLabel = 'Hero Sliders';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('subtitle')
                    ->label('Small label above title')
                    ->placeholder('Premium Collection')
                    ->maxLength(255),
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('description')->rows(2)->columnSpanFull(),
                TextInput::make('button_text')->placeholder('Shop Now')->maxLength(255),
                TextInput::make('button_url')->placeholder('/shop')->maxLength(255),
                FileUpload::make('image')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('sliders')
                    ->helperText('Wide image, 1920×1080 recommended.')
                    ->required()
                    ->columnSpanFull(),
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
                ImageColumn::make('image')->disk('public')->width(120)->height(60),
                TextColumn::make('title')->searchable(),
                TextColumn::make('subtitle')->toggleable(),
                TextColumn::make('button_url')->label('Link')->toggleable(),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageHeroSliders::route('/')];
    }
}
