<?php

namespace App\Filament\Resources\ContentPages;

use App\Filament\Resources\ContentPages\Pages\ManageContentPages;
use App\Models\ContentPage;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContentPageResource extends Resource
{
    protected static ?string $model = ContentPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Customer Service Pages';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('slug')
                    ->label('Page')
                    ->options([
                        'customer-service' => 'Customer Service',
                        'faq' => 'FAQ (intro text only — questions live under FAQ Items)',
                        'shipping-info' => 'Shipping Information',
                        'returns-exchanges' => 'Returns & Exchanges',
                        'privacy-policy' => 'Privacy Policy',
                        'terms-and-conditions' => 'Terms & Conditions',
                    ])
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit'),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                RichEditor::make('content')
                    ->required()
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('content-pages'),
                Select::make('status')
                    ->options([
                        ContentPage::STATUS_DRAFT => 'Draft',
                        ContentPage::STATUS_PUBLISHED => 'Published',
                    ])
                    ->required()
                    ->default(ContentPage::STATUS_DRAFT)
                    ->helperText('Only published pages are visible on the storefront.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('slug')->badge(),
                BadgeColumn::make('status')
                    ->colors([
                        'gray' => ContentPage::STATUS_DRAFT,
                        'success' => ContentPage::STATUS_PUBLISHED,
                    ]),
                TextColumn::make('updatedBy.name')->label('Last updated by')->placeholder('—'),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    ContentPage::STATUS_DRAFT => 'Draft',
                    ContentPage::STATUS_PUBLISHED => 'Published',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
            // No delete action: these six slugs are the fixed set of pages the
            // storefront links to. Deleting one would 404 a live page, so the
            // only safe lifecycle action here is publish/unpublish via `status`.
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageContentPages::route('/'),
        ];
    }
}
