<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomRequestResource\Pages;
use App\Filament\Resources\CustomRequestResource\RelationManagers;
use App\Models\CustomRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomRequestResource extends Resource
{
    protected static ?string $model = CustomRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin' || auth()->user()?->role === 'vendor';
    }

    public static function getNavigationLabel(): string
    {
        return auth()->user()?->role === 'vendor' ? 'Chat' : 'Pedidos Especiales';
    }

    public static function getModelLabel(): string
    {
        return auth()->user()?->role === 'vendor' ? 'Chat' : 'Pedido Especial';
    }

    public static function getPluralModelLabel(): string
    {
        return auth()->user()?->role === 'vendor' ? 'Chats' : 'Pedidos Especiales';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                
                Tables\Columns\TextColumn::make('reply_status')
                    ->label('Conversación')
                    ->badge()
                    ->state(function (CustomRequest $record): string {
                        $messages = $record->messages()->where('is_system_message', false)->latest()->get();
                        if ($messages->isEmpty()) {
                            return 'Nueva';
                        }
                        
                        $latest = $messages->first();
                        if ($latest->sender_type === 'admin') {
                            return 'Respondido';
                        }
                        
                        return 'Pendiente';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Nueva' => 'info',
                        'Respondido' => 'success',
                        'Pendiente' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Nueva' => 'heroicon-o-chat-bubble-left-right',
                        'Respondido' => 'heroicon-o-arrow-turn-down-left',
                        'Pendiente' => 'heroicon-o-bell-alert',
                        default => 'heroicon-o-chat-bubble-left',
                    }),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado de Pedido')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'quoted' => 'info',
                        'accepted' => 'success',
                        'closed' => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('last_message')
                    ->label('Último Mensaje')
                    ->state(function (CustomRequest $record) {
                        $lastMsg = $record->messages()->where('is_system_message', false)->latest()->first();
                        return $lastMsg ? \Illuminate\Support\Str::limit($lastMsg->message ?? '', 40) : '—';
                    })
                    ->description(function (CustomRequest $record) {
                        $lastMsg = $record->messages()->where('is_system_message', false)->latest()->first();
                        if (!$lastMsg) return null;
                        return $lastMsg->created_at->diffForHumans();
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('has_unread_admin', 'desc')
            ->actions([
                Tables\Actions\Action::make('chat')
                    ->label('Abrir Chat')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->url(fn (CustomRequest $record): string => static::getUrl('chat', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if (auth()->user() && auth()->user()->role === 'vendor') {
            $query->where('vendor_id', auth()->id());
        } elseif (auth()->user() && auth()->user()->role === 'admin') {
            // El admin ve solo los que no tienen vendor_id (chat global)
            $query->whereNull('vendor_id');
        }
        
        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomRequests::route('/'),
            'chat' => Pages\ChatCustomRequest::route('/{record}/chat'),
        ];
    }
}
