<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        // Mostrar productos solo a admin y vendors
        return $user && in_array($user->role, ['admin', 'vendor']);
    }
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Productos';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel = 'Productos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Textarea::make('description')
                    ->nullable()
                    ->rows(5),
                Forms\Components\TextInput::make('price')->numeric()->prefix('$')->required(),
                Forms\Components\TextInput::make('stock')->numeric()->default(1),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->visibility('public')
                    ->nullable(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Visible (Publicado)')
                    ->default(true)
                    ->disabled(fn () => auth()->user() && auth()->user()->role !== 'admin'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        
        return $table
            ->defaultPaginationPageOption(10)  // OPTIMIZATION: Show fewer items per page
            ->columns([
                // Commenting out ImageColumn for now - loading images causes slowness
                // Tables\Columns\ImageColumn::make('image')
                //     ->label('Imagen'),

                // Muestra el nombre del producto
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                // Muestra el precio formateado
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('ARS')
                    ->sortable(),

                // Muestra el stock
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable(),

                // Muestra el nombre del vendedor solo para admin
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Vendedor')
                    ->visible($user && $user->role === 'admin'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Visible')
                    ->disabled(fn () => $user && $user->role !== 'admin'),
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
                    Tables\Actions\BulkAction::make('ocultar')
                        ->label('Ocultar seleccionados')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('mostrar')
                        ->label('Mostrar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->with('user');
        if ($user && $user->role === 'vendor') {
            return $query->where('user_id', $user->id);
        }
        return $query;
    }
}
