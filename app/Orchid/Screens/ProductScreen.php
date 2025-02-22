<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use App\Models\Product;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;

class ProductScreen extends Screen
{
    public function query(): array
    {
        return ['products' => Product::paginate(10)];
    }

    public function name(): string
    {
        return 'Product Management';
    }

    public function commandBar(): array
    {
        return [
            ModalToggle::make('Add Product')
                ->modal('createProductModal')
                ->method('create')
                ->icon('plus')
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('products', [
                TD::make('id', 'ID')->sort(),
                TD::make('name', 'Product Name')->sort(),
                TD::make('category', 'Category'),
                TD::make('price', 'Price')->sort(),
                TD::make('stock', 'Stock')->sort(),
                TD::make('actions', 'Actions')->render(fn ($product) =>
                    ModalToggle::make('Edit')
                        ->modal('editProductModal')
                        ->method('update')
                        ->modalTitle('Edit Product')
                        ->asyncParameters(['product' => $product->id])
                        ->icon('pencil')
                        ->class('btn btn-primary') . ' ' .
                    Button::make('Delete')
                        ->method('delete', ['id' => $product->id])
                        ->icon('trash')
                        ->confirm('Are you sure you want to delete this product?')
                        ->class('btn btn-danger')
                ),
            ]),

            Layout::modal('createProductModal', Layout::rows([
                Input::make('product.name')->title('Product Name')->placeholder('Enter product name'),
                Input::make('product.category')->title('Category')->placeholder('Enter category'),
                Input::make('product.price')->title('Price')->type('number')->step(0.01),
                Input::make('product.stock')->title('Stock')->type('number'),
            ]))->title('Create Product')->applyButton('Save'),

            Layout::modal('editProductModal', Layout::rows([
                Input::make('product.name')->title('Product Name')->placeholder('Update product name'),
                Input::make('product.category')->title('Category')->placeholder('Update category'),
                Input::make('product.price')->title('Price')->type('number')->step(0.01),
                Input::make('product.stock')->title('Stock')->type('number'),
            ]))->title('Edit Product')->applyButton('Update')->async('asyncGetProduct'),
        ];
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'product.name' => 'required',
            'product.category' => 'required',
            'product.price' => 'required|numeric',
            'product.stock' => 'required|integer',
        ]);

        Product::create([
            'name' => $validated['product']['name'],
            'category' => $validated['product']['category'],
            'price' => $validated['product']['price'],
            'stock' => $validated['product']['stock'],
        ]);

        Toast::info('Product Added!');
        return redirect()->route('platform.product');
    }

    public function asyncGetProduct(Product $product): array
    {
        return ['product' => $product];
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product.name' => 'required',
            'product.category' => 'required',
            'product.price' => 'required|numeric',
            'product.stock' => 'required|integer',
        ]);

        $product->update([
            'name' => $validated['product']['name'],
            'category' => $validated['product']['category'],
            'price' => $validated['product']['price'],
            'stock' => $validated['product']['stock'],
        ]);

        Toast::info('Product Updated!');
        return redirect()->route('platform.product');
    }

    public function delete(Request $request)
    {
        Product::findOrFail($request->get('id'))->delete();
        Toast::warning('Product Deleted!');
        return redirect()->route('platform.product');
    }
}
