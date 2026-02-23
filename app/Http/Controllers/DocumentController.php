<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Quote;
use App\Models\Sale;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Throwable;

class DocumentController extends Controller
{
    public function saleReceipt(Sale $sale): Response
    {
        $this->ensureCanAccess($sale->user_id);
        abort_unless(Auth::user()?->hasFeature(\App\Models\Feature::SALES) ?? false, 403);

        abort_if($sale->status !== Sale::STATUS_COMPLETED, 422, 'Recibo disponível apenas para vendas concluídas.');

        $sale->loadMissing(['client', 'user', 'recipe']);

        $filename = 'recibo-'.$this->safeCode($sale->reference ?: (string) $sale->id).'.pdf';

        return $this->downloadPdf('documents.sale-receipt', [
            'sale' => $sale,
            'documentTitle' => 'Recibo de Venda',
            'issuedAt' => now(),
            'issuer' => $this->issuerData($sale->user),
        ], $filename);
    }

    public function quotePdf(Quote $quote): Response
    {
        $this->ensureCanAccess($quote->user_id);
        abort_unless(Auth::user()?->hasFeature(\App\Models\Feature::QUOTES) ?? false, 403);

        $quote->loadMissing(['client', 'user', 'items.recipe']);

        $filename = 'cotacao-'.$this->safeCode($quote->reference ?: (string) $quote->id).'.pdf';

        return $this->downloadPdf('documents.quote-document', [
            'quote' => $quote,
            'documentTitle' => 'Cotação',
            'issuedAt' => now(),
            'issuer' => $this->issuerData($quote->user),
        ], $filename);
    }

    public function orderSlip(Order $order): Response
    {
        $this->ensureCanAccess($order->user_id);
        abort_unless(Auth::user()?->hasFeature(\App\Models\Feature::ORDERS) ?? false, 403);

        $order->loadMissing(['client', 'user', 'quote', 'items.recipe']);

        $filename = 'pedido-'.$this->safeCode($order->reference ?: (string) $order->id).'.pdf';

        return $this->downloadPdf('documents.order-slip', [
            'order' => $order,
            'documentTitle' => 'Comprovativo de Pedido',
            'issuedAt' => now(),
            'issuer' => $this->issuerData($order->user),
        ], $filename);
    }

    private function ensureCanAccess(int $ownerId): void
    {
        $user = Auth::user();

        abort_unless($user, 401);
        abort_unless($user->isAdmin() || $user->id === $ownerId, 403);
    }

    /**
     * @return array{name: string, address: string, email: string, contact: string, footer: string, app_name: string}
     */
    private function issuerData(?User $owner = null): array
    {
        $name = trim((string) ($owner?->name ?? ''));
        $email = trim((string) ($owner?->email ?? ''));
        $contact = trim((string) ($owner?->contact_number ?? ''));

        return [
            'name' => $name !== '' ? $name : (string) config('documents.issuer_name', config('app.name')),
            'address' => (string) config('documents.issuer_address', 'Maputo, Moçambique'),
            'email' => $email !== '' ? $email : (string) config('documents.issuer_email', ''),
            'contact' => $contact !== '' ? $contact : (string) config('documents.issuer_contact', ''),
            'footer' => (string) config('documents.footer_text', 'Powered by Cheesemania'),
            'app_name' => (string) config('app.name', 'StockPulse'),
        ];
    }

    private function safeCode(string $value): string
    {
        $clean = preg_replace('/[^A-Za-z0-9\-]/', '-', strtoupper(trim($value)));
        $clean = preg_replace('/-+/', '-', (string) $clean);

        return trim((string) $clean, '-') ?: 'DOC';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function downloadPdf(string $view, array $payload, string $filename): Response
    {
        try {
            return Pdf::loadView($view, $payload)
                ->setPaper('a4')
                ->download($filename);
        } catch (Throwable $exception) {
            report($exception);

            abort(
                500,
                'Falha ao gerar PDF no servidor. Verifique permissões em storage/fonts e storage/app/dompdf/temp.'
            );
        }
    }
}
