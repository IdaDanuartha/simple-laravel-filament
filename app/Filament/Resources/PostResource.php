<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PostResource extends Resource
{
  protected static ?string $model = Post::class;

  protected static ?string $navigationIcon = 'heroicon-o-document-text';

  protected static ?string $navigationGroup = 'Content';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make()
          ->schema([
            Forms\Components\Grid::make(2)
              ->schema([
                Forms\Components\TextInput::make('title')
                  ->required()
                  ->maxLength(100)
                  ->live(onBlur: true)
                  ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')
                  ->required()
                  ->maxLength(100),
              ]),
            Forms\Components\Toggle::make('active')
              ->required(),
            Forms\Components\DateTimePicker::make('published_at')
              ->required(),
            Forms\Components\RichEditor::make('body')
              ->required(),
          ])->columnSpan(8),
        Forms\Components\Section::make()
          ->schema([
            Forms\Components\FileUpload::make('thumbnail')
              ->directory('posts')
              ->visibility('public'),
            Forms\Components\Select::make('category_id')
            ->multiple()
            ->required()
            ->relationship("categories", "title"),
          ])->columnSpan(4)
      ])->columns(12);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\ImageColumn::make('thumbnail')
          ->searchable(),        
        Tables\Columns\TextColumn::make('title')
          ->searchable(),
        // Tables\Columns\TextColumn::make('slug')
        //     ->searchable(),
        Tables\Columns\IconColumn::make('active')
          ->boolean(),
        Tables\Columns\TextColumn::make('published_at')
          ->dateTime()
          ->sortable(),
        Tables\Columns\TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        Tables\Filters\TrashedFilter::make(),
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
        Tables\Actions\DeleteAction::make(),
        Tables\Actions\ForceDeleteAction::make(),
        Tables\Actions\RestoreAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
          Tables\Actions\ForceDeleteBulkAction::make(),
          Tables\Actions\RestoreBulkAction::make(),
        ]),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListPosts::route('/'),
      'create' => Pages\CreatePost::route('/create'),
      'view' => Pages\ViewPost::route('/{record}'),
      'edit' => Pages\EditPost::route('/{record}/edit'),
    ];
  }

  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()
      ->withoutGlobalScopes([
        SoftDeletingScope::class,
      ]);
  }
}
