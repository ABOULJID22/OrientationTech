<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;

class PurchasePdfController extends Controller
{
    /**
     * Génère un PDF d'une Purchase et le télécharge/stream.
     */
   /*  public function download(Purchase $purchase)
    {
        // Buyer à partir de l'utilisateur lié à la purchase (adapter selon vos champs)
        $user = $purchase->user;
        $buyer = new Buyer([
            'name' => $user?->pharmacist_name ?: $user?->name ?: 'Client',
            'custom_fields' => [
                'email' => $user?->email ?: '',
            ],
        ]);

        // Construire les items : si vous avez une relation items, adaptez le mapping
        $items = [];
        if (method_exists($purchase, 'items') && $purchase->relationLoaded('items')) {
            $lines = $purchase->items;
        } else {
            // essayer de charger la relation si existante
            try {
                $lines = $purchase->items ?? null;
            } catch (\Throwable $e) {
                $lines = null;
            }
        }

        if ($lines && $lines->count()) {
            foreach ($lines as $line) {
                // adapter les champs (title, qty, price) selon votre modèle purchase item
                $title = $line->name ?? ($line->product_name ?? 'Ligne');
                $qty = $line->quantity ?? $line->qty ?? 1;
                $price = $line->unit_price ?? $line->price ?? 0;
                $items[] = (new InvoiceItem())->title($title)->pricePerUnit((float) $price)->quantity($qty);
            }
        } else {
            // fallback : résumé de la purchase
            $items[] = (new InvoiceItem())
                ->title("Achat #{$purchase->id}")
                ->pricePerUnit((float) ($purchase->last_order_value ?? 0))
                ->quantity(1);
        }

        // Construire la facture
        $invoice = Invoice::make()
            ->buyer($buyer)
            ->addItems($items)
            // options possibles (ajustez ou retirez)
            ->discountByPercent((float) ($purchase->discount_percent ?? 0))
            ->taxRate((float) ($purchase->tax_rate ?? 0))
            ->shipping((float) ($purchase->shipping ?? 0));

        // choisir download() pour forcer le téléchargement avec un nom, ou stream()
        $filename = "purchase-{$purchase->id}.pdf";
        return $invoice->filename($filename)->download();
    } */
 public function view(Purchase $purchase, string $filename)
    {
        // Autorisation : vérifiez que l'utilisateur peut voir cette purchase
        $this->authorize('view', $purchase);

        $files = (array) ($purchase->attachments ?? []);
        $match = collect($files)->first(fn($path) => basename($path) === $filename);

        if (! $match) {
            abort(404);
        }

        // Si votre disque est local et accessible :
        $fullPath = Storage::disk('public')->path($match);
        if (! file_exists($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
    }

    // Force le téléchargement
    public function download(Purchase $purchase, string $filename)
    {
        $this->authorize('view', $purchase);

        $files = (array) ($purchase->attachments ?? []);
        $match = collect($files)->first(fn($path) => basename($path) === $filename);

        if (! $match) {
            abort(404);
        }

        // Pour S3 on peut utiliser temporaryUrl, sinon Storage::download pour local
        if (Storage::disk('public')->exists($match)) {
            return Storage::disk('public')->download($match, $filename);
        }

        abort(404);
    }
}