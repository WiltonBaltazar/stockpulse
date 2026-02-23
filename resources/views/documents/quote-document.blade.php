<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTitle }} - {{ $quote->reference }}</title>
    @include('documents.partials.base-styles')
</head>
<body>
@php
    $customerName = $quote->client?->name ?: 'Cliente não informado';
    $customerContact = $quote->client?->contact_number ?: '-';
    $customerEmail = $quote->client?->email ?: '-';
    $customerAddress = $quote->client?->address ?: '-';
    $subTotal = (float) $quote->items->sum('total_price');
    $additionalFee = (float) $quote->additional_fee;
    $discount = (float) $quote->discount;
    $total = (float) $quote->total_amount;
    $validUntil = optional($quote->quote_date)->copy()?->addDays(7);
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
                    <span class="label">Cliente</span>
                    <div class="value"><strong>{{ $customerName }}</strong></div>
                    <div class="value">{{ $customerAddress }}</div>
                    <div class="value">{{ $customerEmail }}</div>
                    <div class="value">{{ $customerContact }}</div>
                </div>
            </td>
            <td>
                <div class="panel">
                    <span class="label">Dados da cotação</span>
                    <div class="value"><strong>N.º cotação:</strong> {{ $quote->reference ?: 'N/D' }}</div>
                    <div class="value"><strong>Data:</strong> {{ optional($quote->quote_date)->format('d/m/Y') ?: '-' }}</div>
                    <div class="value"><strong>Validade:</strong> {{ $validUntil?->format('d/m/Y') ?: '-' }}</div>
                    <div class="value"><strong>Tipo:</strong> {{ \App\Models\Quote::typeOptions()[$quote->type] ?? $quote->type }}</div>
                    <div class="value"><strong>Estado:</strong> {{ \App\Models\Quote::statusOptions()[$quote->status] ?? $quote->status }}</div>
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
        @foreach($quote->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $item->resolved_item_name }}</strong>
                    @if ($item->recipe)
                        <div style="font-size:10px;color:#6b7280;">Origem: receita</div>
                    @endif
                </td>
                <td class="num">{{ number_format((int) $item->quantity, 0, ',', '.') }}</td>
                <td class="num">{{ number_format((float) $item->unit_price, 2, ',', '.') }} MT</td>
                <td class="num">{{ number_format((float) $item->total_price, 2, ',', '.') }} MT</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="name">Subtotal</td>
            <td class="amount">{{ number_format($subTotal, 2, ',', '.') }} MT</td>
        </tr>
        <tr>
            <td class="name">Taxa adicional</td>
            <td class="amount">{{ number_format($additionalFee, 2, ',', '.') }} MT</td>
        </tr>
        <tr>
            <td class="name">Desconto</td>
            <td class="amount">- {{ number_format($discount, 2, ',', '.') }} MT</td>
        </tr>
        <tr class="total">
            <td class="name">Total</td>
            <td class="amount">{{ number_format($total, 2, ',', '.') }} MT</td>
        </tr>
    </table>

    <div class="footer">
        <div>Condições: valores válidos até {{ $validUntil?->format('d/m/Y') ?: '-' }}.</div>
        @if (filled($quote->notes))
            <div>Observações: {{ $quote->notes }}</div>
        @endif
        <div>{{ $issuer['app_name'] }} · {{ $issuer['footer'] }}</div>
    </div>
</div>
</body>
</html>
