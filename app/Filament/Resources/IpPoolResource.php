<?php

namespace App\Filament\Resources;

use App\Models\Router;
use App\Filament\Resources\IpPoolResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class IpPoolResource extends Resource
{
    protected static ?string $model = Router::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = 'Mikrotik Management';

    protected static ?string $label = 'IP Pool';

    protected static ?string $slug = 'ip-pools';

    public static function table(Table $table): Table
    {
        return $table
            ->query(Router::query())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Router Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('host')
                    ->label('IP Address')
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
            ])
            ->actions([
                Action::make('manage_pools')
                    ->label('Manage IP Pools')
                    ->icon('heroicon-o-circle-stack')
                    ->url(fn (Router $record) => static::getUrl('manage', ['record' => $record->id])),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRouters::route('/'),
            'manage' => Pages\ManagePools::route('/{record}/manage'),
        ];
    }
}
