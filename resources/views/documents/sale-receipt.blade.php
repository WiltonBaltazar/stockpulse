<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTitle }} - {{ $sale->reference }}</title>
    @include('documents.partials.base-styles')
</head>
<body>
@php
    $customerName = $sale->client?->name ?: ($sale->customer_name ?: 'Cliente não informado');
    $customerContact = $sale->client?->contact_number ?: '-';
    $customerEmail = $sale->client?->email ?: '-';
    $customerAddress = $sale->client?->address ?: '-';
    $itemName = $sale->resolved_item_name;
    $quantity = (int) round((float) $sale->quantity);
    $unitPrice = (float) $sale->unit_price;
    $total = (float) $sale->total_amount;
@endphp
<div class="doc">
    <table class="header">
        <tr>
            <td>
                <h1 class="title">{{ \Illuminate\Support\Str::upper($documentTitle) }}</h1>
                <p class="subtitle">Documento emitido em {{ $issuedAt->format('d/m/Y H:i') }}</p>
            </td>
            <td style="text-align:right;">
                <strong style="font-size:14px;color:#685D94;">{{ $issuer['name'] }}</strong><br>
                <span style="color:#4b5563;">{{ $issuer['address'] }}</span><br>
                @if ($issuer['email'] !== '')
                    <span style="color:#4b5563;">{{ $issuer['email'] }}</span><br>
                @endif
                @if ($issuer['contact'] !== '')
                    <span style="color:#4b5563;">{{ $issuer['contact'] }}</span>
                @endif
            </td>
        </tr>
    </table>

    <table class="grid">
        <tr>
            <td>
                <div class="panel">
                    <span class="label">Faturar para</span>
                    <div class="value"><strong>{{ $customerName }}</strong></div>
                    <div class="value">{{ $customerAddress }}</div>
                    <div class="value">{{ $customerEmail }}</div>
                    <div class="value">{{ $customerContact }}</div>
                </div>
            </td>
            <td>
                <div class="panel">
                    <span class="label">Dados do recibo</span>
                    <div class="value"><strong>N.º recibo:</strong> {{ $sale->reference ?: 'N/D' }}</div>
                    <div class="value"><strong>Data da venda:</strong> {{ optional($sale->sold_at)->format('d/m/Y H:i') ?: '-' }}</div>
                    <div class="value"><strong>Método de pagamento:</strong> {{ \App\Models\Sale::paymentOptions()[$sale->payment_method] ?? $sale->payment_method }}</div>
                    <div class="value"><strong>Canal:</strong> {{ \App\Models\Sale::channelOptions()[$sale->channel] ?? $sale->channel }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
        <tr>
            <th style="width:60px;">S.No</th>
            <th>Produto</th>
            <th class="num" style="width:90px;">Qtd.</th>
            <th class="num" style="width:130px;">Valor unitário</th>
            <th class="num" style="width:130px;">Valor total</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>1</td>
            <td>
                <strong>{{ $itemName }}</strong>
                @if ($sale->recipe)
                    <div style="font-size:10px;color:#6b7280;">Origem: receita</div>
                @endif
                @if (filled($sale->notes))
                    <div style="font-size:10px;color:#6b7280;">Obs.: {{ $sale->notes }}</div>
                @endif
            </td>
            <td class="num">{{ number_format($quantity, 0, ',', '.') }}</td>
            <td class="num">{{ number_format($unitPrice, 2, ',', '.') }} MT</td>
            <td class="num">{{ number_format($total, 2, ',', '.') }} MT</td>
        </tr>
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="name">Subtotal</td>
            <td class="amount">{{ number_format($total, 2, ',', '.') }} MT</td>
        </tr>
        <tr>
            <td class="name">Taxas adicionais</td>
            <td class="amount">0,00 MT</td>
        </tr>
        <tr class="total">
            <td class="name">Total</td>
            <td class="amount">{{ number_format($total, 2, ',', '.') }} MT</td>
        </tr>
    </table>

    <div class="footer">
        Registado por {{ $sale->user?->name ?? 'Utilizador' }}.
        Estado da venda: {{ \App\Models\Sale::statusOptions()[$sale->status] ?? $sale->status }}.
        {{ $issuer['app_name'] }} · {{ $issuer['footer'] }}
    </div>
</div>
</body>
</html>
