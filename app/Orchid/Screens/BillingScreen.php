<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use App\Models\Billing;
use App\Models\Client;
use App\Models\Product;
use App\Models\BillingItem;
use Illuminate\Http\Request;

class BillingScreen extends Screen
{
    public function query(): array
    {
        return ['billings' => Billing::paginate()];
    }

    public function name(): string
    {
        return 'Billing Management';
    }

    public function commandBar(): array
    {
        return [
            Button::make('Create Bill')->method('create'),
        ];
    }

    public function layout(): array
    {
        return [
            Table::make('billings')->columns([
                TD::make('id', 'ID')->sort(),
                TD::make('client.name', 'Client Name'),
                TD::make('client.phone', 'Phone Number'),
                TD::make('client.pet_name', 'Pet Name'),
                TD::make('total_amount', 'Total Amount'),
                TD::make('payment_status', 'Status'),
                TD::make('actions', 'Actions')->render(function ($bill) {
                    return Button::make('Mark as Paid')->method('markPaid', ['id' => $bill->id]);
                }),
            ])
        ];
    }

    public function create(Request $request)
    {
        $subtotal = 0;
        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $subtotal += $product->price * $item['quantity'];
        }

        $billing = Billing::create([
            'client_id' => $request->client_id,
            'total_amount' => $subtotal,
            'payment_status' => 'Pending',
        ]);

        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($product->stock < $item['quantity']) {
                return response()->json(['error' => 'Not enough stock for ' . $product->name], 400);
            }

            BillingItem::create([
                'billing_id' => $billing->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);

            $product->decrement('stock', $item['quantity']);
        }

        Toast::info('Bill Created!');
        return redirect()->route('platform.billing');
    }

    public function markPaid(Request $request)
    {
        Billing::find($request->get('id'))->update(['payment_status' => 'Paid']);
        Toast::success('Payment Marked as Paid!');
        return redirect()->route('platform.billing');
    }
}
