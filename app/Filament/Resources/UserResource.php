<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->options([
                        'admin' => 'Administrador',
                        'vendor' => 'Vendedor',
                        'delivery' => 'Repartidor',
                        'customer' => 'Cliente',
                    ])
                    ->required()
                    ->live(),
                Forms\Components\Toggle::make('is_approved')
                    ->label('Aprobado')
                    ->default(false),
                Forms\Components\TextInput::make('address')
                    ->label('Dirección (vendedores)')
                    ->maxLength(255)
                    ->hidden(fn (\Filament\Forms\Get $get) => $get('role') !== 'vendor'),
                Forms\Components\TimePicker::make('opening_time')
                    ->label('Hora de Apertura (vendedores)')
                    ->seconds(false)
                    ->nullable()
                    ->hidden(fn (\Filament\Forms\Get $get) => $get('role') !== 'vendor'),
                Forms\Components\TimePicker::make('closing_time')
                    ->label('Hora de Cierre (vendedores)')
                    ->seconds(false)
                    ->nullable()
                    ->hidden(fn (\Filament\Forms\Get $get) => $get('role') !== 'vendor'),
                Forms\Components\TimePicker::make('opening_time_2')
                    ->label('Hora de Apertura - Turno 2 (vendedores)')
                    ->seconds(false)
                    ->nullable()
                    ->hidden(fn (\Filament\Forms\Get $get) => $get('role') !== 'vendor'),
                Forms\Components\TimePicker::make('closing_time_2')
                    ->label('Hora de Cierre - Turno 2 (vendedores)')
                    ->seconds(false)
                    ->nullable()
                    ->hidden(fn (\Filament\Forms\Get $get) => $get('role') !== 'vendor'),
                Forms\Components\CheckboxList::make('closed_days')
                    ->label('Días Cerrado (vendedores)')
                    ->options([
                        'Monday' => 'Lunes',
                        'Tuesday' => 'Martes',
                        'Wednesday' => 'Miércoles',
                        'Thursday' => 'Jueves',
                        'Friday' => 'Viernes',
                        'Saturday' => 'Sábado',
                        'Sunday' => 'Domingo',
                    ])
                    ->columns(4)
                    ->nullable()
                    ->hidden(fn (\Filament\Forms\Get $get) => $get('role') !== 'vendor'),
                Forms\Components\TextInput::make('latitude')
                    ->label('Latitud')
                    ->numeric()
                    ->hidden(fn (\Filament\Forms\Get $get) => $get('role') !== 'vendor'),
                Forms\Components\TextInput::make('longitude')
                    ->label('Longitud')
                    ->numeric()
                    ->hidden(fn (\Filament\Forms\Get $get) => $get('role') !== 'vendor'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge(),
                Tables\Columns\ToggleColumn::make('is_approved')
                    ->label('Aprobado'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
