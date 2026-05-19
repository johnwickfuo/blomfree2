<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    // Orders are placed via the public checkout, never created from the admin.
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->form([
                    Forms\Components\DatePicker::make('date_from')
                        ->label('From')
                        ->default(now()->subDays(30)->startOfDay()),
                    Forms\Components\DatePicker::make('date_to')
                        ->label('To')
                        ->default(now()->endOfDay()),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->multiple()
                        ->options([
                            'pending_payment' => 'Pending payment',
                            'paid' => 'Paid',
                            'processing' => 'Processing',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                            'refunded' => 'Refunded',
                        ])
                        ->placeholder('All statuses'),
                ])
                ->action(fn (array $data): StreamedResponse => $this->exportCsv($data)),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function exportCsv(array $data): StreamedResponse
    {
        $query = Order::query()
            ->with('items')
            ->orderByDesc('placed_at');

        if (! empty($data['date_from'])) {
            $query->whereDate('placed_at', '>=', $data['date_from']);
        }
        if (! empty($data['date_to'])) {
            $query->whereDate('placed_at', '<=', $data['date_to']);
        }
        if (! empty($data['status'])) {
            $query->whereIn('order_status', $data['status']);
        }

        $filename = 'orders-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Reference', 'Placed at', 'Paid at',
                'Customer', 'Email', 'Phone',
                'Delivery method', 'State', 'LGA', 'Address',
                'Subtotal', 'Shipping', 'Total',
                'Gateway', 'Payment status', 'Order status', 'Items',
            ]);

            $query->chunk(200, function ($orders) use ($handle): void {
                foreach ($orders as $order) {
                    $items = $order->items
                        ->map(fn ($i): string => $i->quantity.'× '.$i->item_name.($i->item_label ? ' ('.$i->item_label.')' : ''))
                        ->implode('; ');

                    fputcsv($handle, [
                        $order->reference,
                        $order->placed_at?->format('Y-m-d H:i'),
                        $order->paid_at?->format('Y-m-d H:i'),
                        $order->customer_name,
                        $order->customer_email,
                        $order->customer_phone,
                        $order->delivery_method,
                        $order->delivery_state,
                        $order->delivery_lga,
                        $order->delivery_address,
                        (float) $order->subtotal,
                        (float) $order->shipping_fee,
                        (float) $order->total,
                        $order->payment_gateway,
                        $order->payment_status,
                        $order->order_status,
                        $items,
                    ]);
                }
            });

            fclose($handle);
        }, $filename);
    }
}
