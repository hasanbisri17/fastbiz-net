<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RouterResource\Pages;
use App\Models\Router;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use App\Services\RouterOSService;
use Filament\Notifications\Notification;

class RouterResource extends Resource
{
    protected static ?string $model = Router::class;

    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static ?string $navigationGroup = 'Mikrotik Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('host')
                    ->label('IP Address')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('port')
                    ->numeric()
                    ->default(8728),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('host')
                    ->label('IP Address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('port'),
                Tables\Columns\IconColumn::make('status')
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
                //
            ])
            ->actions([
                Action::make('test_connection')
                    ->label('Test Connection')
                    ->icon('heroicon-o-signal')
                    ->action(function (Router $router, RouterOSService $service) {
                        try {
                            $isConnected = $service->testConnection(
                                $router->host,
                                $router->username,
                                $router->password,
                                $router->port
                            );
                            
                            if ($isConnected) {
                                Notification::make()
                                    ->title('Connection Successful')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Connection Failed')
                                    ->danger()
                                    ->body('Could not connect to the router. Please check credentials and try again.')
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Connection Error')
                                ->danger()
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
                Action::make('check_resources')
                    ->label('Check Resources')
                    ->icon('heroicon-o-cpu-chip')
                    ->action(function (Router $router, RouterOSService $service) {
                        try {
                            $service->connect(
                                $router->host,
                                $router->username,
                                $router->password,
                                $router->port
                            );
                            
                            $resources = $service->query('/system/resource/print')->read();
                            
                            if (isset($resources[0])) {
                                $resourceInfo = "CPU Load: {$resources[0]['cpu-load']}%\n";
                                $resourceInfo .= "Free Memory: {$resources[0]['free-memory']} bytes\n";
                                $resourceInfo .= "Total Memory: {$resources[0]['total-memory']} bytes\n";
                                $resourceInfo .= "Free HDD Space: {$resources[0]['free-hdd-space']} bytes\n";
                                $resourceInfo .= "Total HDD Space: {$resources[0]['total-hdd-space']} bytes";
                            } else {
                                $resourceInfo = "No resource information available";
                            }
                            
                            Notification::make()
                                ->title('System Resources')
                                ->success()
                                ->body($resourceInfo)
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error Fetching Resources')
                                ->danger()
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
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
            'index' => Pages\ListRouters::route('/'),
            'create' => Pages\CreateRouter::route('/create'),
            'edit' => Pages\EditRouter::route('/{record}/edit'),
        ];
    }
}
