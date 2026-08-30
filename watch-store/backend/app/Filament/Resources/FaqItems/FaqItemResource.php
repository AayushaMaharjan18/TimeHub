<?php

namespace App\Filament\Resources\FaqItems;

use App\Filament\Resources\FaqItems\Pages\ManageFaqItems;
use App\Models\FaqCategory;
use App\Models\FaqItem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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

class FaqItemResource extends Resource
{
    protected static ?string $model = FaqItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'FAQ Items';

    protected static ?string $recordTitleAttribute = 'question';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('faq_category_id')
                ->label('Category')
                ->options(fn () => FaqCategory::orderBy('sort_order')->pluck('name', 'id'))
                ->searchable()
                ->placeholder('Uncategorized'),
            TextInput::make('question')->required()->maxLength(500)->columnSpanFull(),
            RichEditor::make('answer')->required()->columnSpanFull(),
            TextInput::make('sort_order')->numeric()->default(0),
            Select::make('status')
                ->options([
                    FaqItem::STATUS_DRAFT => 'Draft',
                    FaqItem::STATUS_PUBLISHED => 'Published',
                ])
                ->default(FaqItem::STATUS_PUBLISHED)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')->searchable()->limit(60),
                TextColumn::make('category.name')->label('Category')->placeholder('Uncategorized'),
                BadgeColumn::make('status')->colors([
                    'gray' => FaqItem::STATUS_DRAFT,
                    'success' => FaqItem::STATUS_PUBLISHED,
                ]),
                TextColumn::make('sort_order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('status')->options([
                    FaqItem::STATUS_DRAFT => 'Draft',
                    FaqItem::STATUS_PUBLISHED => 'Published',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFaqItems::route('/'),
        ];
    }
}
