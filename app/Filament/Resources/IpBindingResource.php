<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IpBindingResource\Pages;
use App\Models\IpBinding;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IpBindingResource extends Resource
{
    protected static ?string $model = IpBinding::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Network Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('router_id')
                    ->relationship('router', 'name')
                    ->required(),
                Forms\Components\TextInput::make('mac_address')
                    ->maxLength(255)
                    ->placeholder('00:11:22:33:44:55'),
                Forms\Components\TextInput::make('ip_address')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('192.168.1.100'),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'regular' => 'Regular',
                        'bypassed' => 'Bypassed',
                        'blocked' => 'Blocked',
                    ])
                    ->default('bypassed'),
                Forms\Components\TextInput::make('comment')
                    ->maxLength(255),
                Forms\Components\Toggle::make('disabled')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('router.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mac_address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'regular' => 'gray',
                        'bypassed' => 'success',
                        'blocked' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('comment')
                    ->searchable(),
                Tables\Columns\IconColumn::make('disabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('router')
                    ->relationship('router', 'name'),
                Tables\Filters\TernaryFilter::make('disabled'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIpBindings::route('/'),
            'create' => Pages\CreateIpBinding::route('/create'),
            'edit' => Pages\EditIpBinding::route('/{record}/edit'),
        ];
    }
}
