<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Articles';

    protected static ?string $modelLabel = 'Article';

    protected static ?string $pluralModelLabel = 'Articles';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('title')->label('Titre')->disabled(),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'draft'     => 'Brouillon',
                        'published' => 'Publié',
                        'archived'  => 'Archivé',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Titre')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('user.name')->label('Auteur')->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Catégorie'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft'     => 'warning',
                        'published' => 'success',
                        'archived'  => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'draft'     => 'Brouillon',
                        'published' => 'Publié',
                        'archived'  => 'Archivé',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('published_at')->label('Publié le')->dateTime('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft'     => 'Brouillon',
                        'published' => 'Publié',
                        'archived'  => 'Archivé',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->label('Publier')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn (BlogPost $record) => $record->status !== 'published')
                    ->action(fn (BlogPost $record) => $record->update(['status' => 'published', 'published_at' => now()])),

                Tables\Actions\Action::make('unpublish')
                    ->label('Dépublier')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->visible(fn (BlogPost $record) => $record->status === 'published')
                    ->action(fn (BlogPost $record) => $record->update(['status' => 'draft'])),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
        ];
    }
}
