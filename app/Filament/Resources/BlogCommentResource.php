<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogCommentResource\Pages;
use App\Models\BlogComment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BlogCommentResource extends Resource
{
    protected static ?string $model = BlogComment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationLabel = 'Commentaires';

    protected static ?string $modelLabel = 'Commentaire';

    protected static ?string $pluralModelLabel = 'Commentaires';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Textarea::make('content')->label('Contenu')->disabled()->rows(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Auteur')->sortable(),
                Tables\Columns\TextColumn::make('post.title')->label('Article')->limit(40),
                Tables\Columns\TextColumn::make('content')->label('Commentaire')->limit(60),
                Tables\Columns\IconColumn::make('moderated_at')
                    ->label('Approuvé')
                    ->boolean()
                    ->getStateUsing(fn (BlogComment $record) => ! is_null($record->moderated_at)),
                Tables\Columns\TextColumn::make('created_at')->label('Soumis le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('pending')
                    ->label('En attente')
                    ->query(fn ($query) => $query->whereNull('moderated_at')),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (BlogComment $record) => is_null($record->moderated_at))
                    ->action(fn (BlogComment $record) => $record->update(['moderated_at' => now()])),

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
            'index' => Pages\ListBlogComments::route('/'),
        ];
    }
}
