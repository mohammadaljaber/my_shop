<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Brand;
use App\Models\Coupon;
use App\Models\Product;
use Filament\Forms\Get;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\DiscountType;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\MorphToSelect;
use App\Filament\Resources\CouponResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CouponResource\RelationManagers;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $navigationGroup='Coupons & Offers Management';
    protected static ?string $navigationIcon = 'heroicon-o-ticket';


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count()==0?'danger':(static::getModel()::count()<3?'warning':'success');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                forms\Components\Section::make('')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->maxLength(6)
                        ->minLength(6),
                    Forms\Components\DatePicker::make('start_date')
                        ->required()
                        ->native(false)
                        ->maxDate(fn(Get $get)=>$get('end_date'))
                        ->live(),
                    Forms\Components\DatePicker::make('end_date')
                        ->minDate(fn(Get $get)=>$get('start_date'))
                        ->required()
                        ->native(false)
                        ->live(),
                    Forms\Components\TextInput::make('times')
                        ->required()
                        ->numeric(),
                ])->columns(5),
                forms\Components\Section::make('')
                ->schema([
                    Forms\Components\TextInput::make('discount')
                        ->required()
                        ->numeric(),
                    Forms\Components\Select::make('discount_type')
                        ->options([
                            DiscountType::PERCENTAGE->value=>"PERCENTAGE",
                            DiscountType::VALUE->value=>"VALUE"
                        ])
                        ->native(false)
                        ->required(),
                    Forms\Components\MorphToSelect::make('couponable')
                        ->types([
                            MorphToSelect\Type::make(Product::class)
                            ->titleAttribute('name'),
                            MorphToSelect\Type::make(Brand::class)
                            ->titleAttribute('name'),
                            MorphToSelect\Type::make(Category::class)
                            ->titleAttribute('name')
                        ])
                        ->native(false)
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Toggle::make('status')
                        ->required(),
                ])->columns(4),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->numeric()
                    ->prefix(
                        fn($record)=>$record->discount_type==DiscountType::PERCENTAGE->value?'% ':(
                            $record->discount_type==DiscountType::VALUE->value?'$ ':''
                        )
                    ),
                Tables\Columns\TextColumn::make('couponable_type')
                    ->label('Type')
                    ->formatStateUsing(
                        fn($record)=>Str::afterLast($record->couponable_type, '\\')
                    )
                    ->badge()
                    ->color(function($record){
                        return match(Str::afterLast($record->couponable_type, '\\')){
                            'Brand' => 'danger',
                            'Product' => 'warning',
                            'Category' => 'success',
                        };
                    }),
                Tables\Columns\TextColumn::make('couponable.name')
                    ->numeric()
                    ->sortable(),
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
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            // 'view' => Pages\ViewCoupon::route('/{record}'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
