<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages;
use App\Mail\ProfileVerificationApproved;
use App\Mail\ProfileVerificationRejected;
use App\Models\ModerationLog;
use App\Models\Profile;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Profils';

    protected static ?string $modelLabel = 'Profil';

    protected static ?string $pluralModelLabel = 'Profils';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('full_name')->label('Nom complet')->disabled(),
                Forms\Components\TextInput::make('job_title')->label('Poste')->disabled(),
                Forms\Components\TextInput::make('country')->label('Pays')->disabled(),
                Forms\Components\Toggle::make('is_verified')->label('Vérifié'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')->label('Avatar')->circular(),
                Tables\Columns\TextColumn::make('full_name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('job_title')->label('Poste')->searchable(),
                Tables\Columns\TextColumn::make('country')->label('Pays')->sortable(),
                Tables\Columns\TextColumn::make('sector.name')->label('Secteur')->sortable(),
                Tables\Columns\IconColumn::make('is_verified')->label('Vérifié')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime('d/m/Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_verified')->label('Vérification'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Profile $record) => ! $record->is_verified)
                    ->action(function (Profile $record) {
                        $record->update([
                            'is_verified' => true,
                            'verified_at' => now(),
                        ]);

                        ModerationLog::create([
                            'admin_user_id' => Auth::id(),
                            'action'        => 'profile_approved',
                            'subject_type'  => Profile::class,
                            'subject_id'    => $record->id,
                        ]);

                        $record->load('user');
                        Mail::to($record->user)->queue(new ProfileVerificationApproved($record));

                        Notification::make()->title('Profil approuvé')->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Raison du rejet')
                            ->placeholder('Expliquez pourquoi le profil est rejeté (optionnel)')
                            ->rows(3),
                    ])
                    ->visible(fn (Profile $record) => $record->is_verified)
                    ->action(function (Profile $record, array $data) {
                        $record->update([
                            'is_verified' => false,
                            'verified_at' => null,
                        ]);

                        ModerationLog::create([
                            'admin_user_id' => Auth::id(),
                            'action'        => 'profile_rejected',
                            'subject_type'  => Profile::class,
                            'subject_id'    => $record->id,
                            'notes'         => $data['reason'] ?? null,
                        ]);

                        $record->load('user');
                        Mail::to($record->user)->queue(new ProfileVerificationRejected($record, $data['reason'] ?? null));

                        Notification::make()->title('Profil rejeté')->warning()->send();
                    }),

                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListProfiles::route('/'),
            'view'  => Pages\ViewProfile::route('/{record}'),
        ];
    }
}
