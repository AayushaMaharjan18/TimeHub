<?php

namespace App\Filament\Resources\Blogs;

use App\Filament\Resources\Blogs\Pages\CreateBlog;
use App\Filament\Resources\Blogs\Pages\EditBlog;
use App\Filament\Resources\Blogs\Pages\ListBlogs;
use App\Models\Blog;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Blog Posts';

    protected static ?string $modelLabel = 'Blog Post';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Post')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set, string $operation) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                        Textarea::make('excerpt')->rows(2)->helperText('Short summary shown on blog cards.'),
                        RichEditor::make('content')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('blogs'),
                    ]),
                Section::make('Publishing')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('status')
                            ->options(['draft' => 'Draft', 'published' => 'Published'])
                            ->default('draft')
                            ->required(),
                        DateTimePicker::make('published_at')->default(now())
                            ->helperText('Future dates are scheduled.'),
                        TextInput::make('author')->maxLength(255),
                        FileUpload::make('featured_image')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('blogs'),
                        TextInput::make('meta_title')->maxLength(255),
                        Textarea::make('meta_description')->rows(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('featured_image')->disk('public')->width(80)->height(50),
                TextColumn::make('title')->searchable()->limit(50),
                TextColumn::make('author')->toggleable(),
                TextColumn::make('status')->badge()
                    ->color(fn (string $state) => $state === 'published' ? 'success' : 'gray'),
                TextColumn::make('published_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBlogs::route('/'),
            'create' => CreateBlog::route('/create'),
            'edit' => EditBlog::route('/{record}/edit'),
        ];
    }
}
