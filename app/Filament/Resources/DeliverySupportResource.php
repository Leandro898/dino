<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliverySupportResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliverySupportResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Soporte Repartidores';
    protected static ?string $pluralModelLabel = 'Soporte Repartidores';
    protected static ?string $modelLabel = 'Soporte Repartidor';
    protected static ?string $navigationGroup = 'Soporte';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('reply_status')
                    ->label('Estado Soporte')
                    ->badge()
                    ->state(function (User $record): string {
                        $lastMsg = \App\Models\SupportMessage::where('delivery_user_id', $record->id)->latest()->first();
                        if (!$lastMsg) {
                            return 'Sin Mensajes';
                        }
                        if ($lastMsg->sender_id === $record->id) {
                            return 'Pendiente';
                        }
                        return 'Respondido';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Sin Mensajes' => 'gray',
                        'Respondido' => 'success',
                        'Pendiente' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Sin Mensajes' => 'heroicon-o-chat-bubble-left',
                        'Respondido' => 'heroicon-o-arrow-turn-down-left',
                        'Pendiente' => 'heroicon-o-bell-alert',
                        default => 'heroicon-o-chat-bubble-left',
                    }),
                
                Tables\Columns\TextColumn::make('last_message')
                    ->label('Último Mensaje')
                    ->state(function (User $record) {
                        $lastMsg = \App\Models\SupportMessage::where('delivery_user_id', $record->id)->latest()->first();
                        return $lastMsg ? \Illuminate\Support\Str::limit($lastMsg->message, 40) : '—';
                    })
                    ->description(function (User $record) {
                        $lastMsg = \App\Models\SupportMessage::where('delivery_user_id', $record->id)->latest()->first();
                        return $lastMsg ? $lastMsg->created_at->diffForHumans() : null;
                    }),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('chat')
                    ->label('Abrir Chat')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->url(fn (User $record): string => static::getUrl('chat', ['record' => $record])),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'delivery');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliverySupports::route('/'),
            'chat' => Pages\ChatDeliverySupport::route('/{record}/chat'),
        ];
    }
}
