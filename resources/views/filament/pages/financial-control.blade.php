<x-filament-panels::page>
    <style>
        .fc-wrap {
            --fc-bg: #f0eff4;
            --fc-card: #ffffff;
            --fc-surface: #f0eff4;
            --fc-text: #000000;
            --fc-muted: #685d94;
            --fc-border: #cec7e5;
            --fc-primary: #685d94;
            --fc-primary-soft: #e0dde9;
            --fc-primary-strong: #000000;
            --fc-success: #047857;
            --fc-danger: #b91c1c;
            --fc-warning: #685d94;
            --fc-info: #685d94;
            --fc-neutral: #685d94;
            border: 1px solid var(--fc-border);
            background: var(--fc-bg);
            border-radius: 1.25rem;
            padding: 1.25rem;
            color: var(--fc-text);
            display: grid;
            gap: 1rem;
        }

        .dark .fc-wrap {
            --fc-bg: #000000;
            --fc-card: #131019;
            --fc-surface: #1e1829;
            --fc-text: #ffffff;
            --fc-muted: #e0dde9;
            --fc-border: #685d94;
            --fc-primary: #cec7e5;
            --fc-primary-soft: #352d4b;
            --fc-primary-strong: #ffffff;
            --fc-success: #34d399;
            --fc-danger: #fca5a5;
            --fc-warning: #cec7e5;
            --fc-info: #e0dde9;
            --fc-neutral: #e0dde9;
        }

        .fc-card {
            border: 1px solid var(--fc-border);
            background: var(--fc-card);
            border-radius: 1rem;
            padding: 1rem;
        }

        .fc-title {
            font-size: 2rem;
            line-height: 1.1;
            font-weight: 700;
            color: var(--fc-text);
            margin: 0;
        }

        .fc-subtitle {
            margin: .5rem 0 0;
            font-size: .95rem;
            color: var(--fc-muted);
        }

        .fc-pills {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .fc-pill {
            border: 1px solid var(--fc-border);
            background: var(--fc-surface);
            color: var(--fc-text);
            border-radius: .75rem;
            padding: .35rem .85rem;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
        }

        .fc-pill:hover {
            border-color: var(--fc-primary);
        }

        .fc-pill-active {
            border-color: var(--fc-primary);
            background: var(--fc-primary-soft);
            color: var(--fc-primary-strong);
        }

        .fc-filters-grid {
            display: grid;
            gap: .75rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 960px) {
            .fc-filters-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }

        .fc-input {
            height: 2.65rem;
            border-radius: .85rem;
            border: 1px solid var(--fc-border);
            background: var(--fc-card);
            color: var(--fc-text);
            padding: 0 .85rem;
            font-size: .95rem;
            width: 100%;
        }

        select.fc-input {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            padding-right: 2.65rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5 7l5 5 5-5' stroke='%23685d94' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-size: 1.05rem 1.05rem;
            background-position: right .85rem center;
        }

        .dark select.fc-input {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none'%3E%3Cpath d='M5 7l5 5 5-5' stroke='%23e0dde9' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        }

        select.fc-input::-ms-expand {
            display: none;
        }

        .fc-input::placeholder {
            color: var(--fc-muted);
        }

        .fc-input:focus {
            outline: 2px solid rgba(104, 93, 148, 0.25);
            border-color: var(--fc-primary);
        }

        .dark .fc-input:focus {
            outline-color: rgba(206, 199, 229, 0.35);
        }

        .fc-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .fc-stats {
            display: grid;
            gap: .75rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 640px) {
            .fc-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1280px) {
            .fc-stats {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .fc-stat-label {
            font-size: .85rem;
            color: var(--fc-muted);
            margin: 0;
        }

        .fc-stat-value {
            margin: .35rem 0;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.1;
            color: var(--fc-text);
        }

        .fc-stat-description {
            margin: 0;
            font-size: .9rem;
            color: var(--fc-muted);
        }

        .fc-tone-success .fc-stat-value { color: var(--fc-success); }
        .fc-tone-danger .fc-stat-value { color: var(--fc-danger); }
        .fc-tone-warning .fc-stat-value { color: var(--fc-warning); }
        .fc-tone-info .fc-stat-value { color: var(--fc-info); }
        .fc-tone-primary .fc-stat-value { color: var(--fc-primary); }
        .fc-tone-gray .fc-stat-value { color: var(--fc-neutral); }

        .fc-panels {
            display: grid;
            gap: .75rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 1280px) {
            .fc-panels {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .fc-panel-title {
            margin: 0;
            font-size: 1.65rem;
            font-weight: 700;
            line-height: 1.2;
            color: var(--fc-text);
        }

        .fc-list {
            display: grid;
            gap: .6rem;
            margin-top: .9rem;
        }

        .fc-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            border: 1px solid var(--fc-border);
            background: var(--fc-surface);
            border-radius: .75rem;
            padding: .65rem .8rem;
        }

        .fc-list-title {
            margin: 0;
            font-size: .95rem;
            font-weight: 600;
            color: var(--fc-text);
        }

        .fc-list-meta {
            margin: .2rem 0 0;
            font-size: .82rem;
            color: var(--fc-muted);
        }

        .fc-list-value {
            font-size: .95rem;
            font-weight: 700;
            color: var(--fc-primary);
        }

        .fc-empty {
            border: 1px dashed var(--fc-border);
            border-radius: .75rem;
            padding: .9rem;
            font-size: .9rem;
            color: var(--fc-muted);
            margin-top: .9rem;
        }

        .fc-table-wrap {
            overflow-x: auto;
            margin-top: .9rem;
        }

        .fc-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 680px;
        }

        .fc-table th,
        .fc-table td {
            border-bottom: 1px solid var(--fc-border);
            padding: .65rem .35rem;
            text-align: left;
            vertical-align: middle;
        }

        .fc-table th {
            font-size: .76rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--fc-muted);
            font-weight: 700;
            white-space: nowrap;
        }

        .fc-table td {
            font-size: .92rem;
            color: var(--fc-text);
        }

        .fc-table tr:last-child td {
            border-bottom: 0;
        }

        .fc-text-right {
            text-align: right !important;
        }

        .fc-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: .16rem .5rem;
            font-size: .74rem;
            font-weight: 700;
        }

        .fc-badge-success {
            color: #065f46;
            background: #d1fae5;
        }

        .fc-badge-warning {
            color: #000000;
            background: #e0dde9;
        }

        .fc-badge-gray {
            color: #374151;
            background: #e5e7eb;
        }

        .dark .fc-badge-success {
            color: #a7f3d0;
            background: #064e3b;
        }

        .dark .fc-badge-warning {
            color: #ffffff;
            background: #685d94;
        }

        .dark .fc-badge-gray {
            color: #d1d5db;
            background: #374151;
        }

        .fc-amount-success {
            color: var(--fc-success) !important;
            font-weight: 700;
        }

        .fc-amount-danger {
            color: var(--fc-danger) !important;
            font-weight: 700;
        }
    </style>

    <div class="fc-wrap">
        <section class="fc-card">
            <div>
                <h2 class="fc-title">Controlo Financeiro</h2>
                <p class="fc-subtitle">Acompanhe receitas, despesas e desempenho do período.</p>
            </div>

            <div class="fc-pills" style="margin-top: .9rem;">
                @foreach ([7 => '7 dias', 30 => '30 dias', 90 => '90 dias', 365 => '12 meses'] as $days => $label)
                    <button
                        type="button"
                        wire:click="setQuickRange({{ $days }})"
                        class="fc-pill {{ $quickRangeDays === $days ? 'fc-pill-active' : '' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="fc-filters-grid" style="margin-top: .9rem;">
                <input type="date" wire:model.live="startDate" class="fc-input" />
                <input type="date" wire:model.live="endDate" class="fc-input" />

                <select wire:model.live="status" class="fc-input">
                    @foreach ($this->statusOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select wire:model.live="source" class="fc-input">
                    @foreach ($this->sourceOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>

                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Pesquisar por cliente, item, referência..."
                    class="fc-input"
                />
            </div>

            <div class="fc-actions" style="margin-top: .9rem;">
                <x-filament::button wire:click="applyFilters" icon="heroicon-m-funnel" color="primary">
                    Aplicar filtros
                </x-filament::button>

                <x-filament::button wire:click="refreshData" icon="heroicon-m-arrow-path" color="gray">
                    Atualizar
                </x-filament::button>
            </div>
        </section>

        <section class="fc-stats">
            @foreach ($this->stats as $stat)
                <article class="fc-card {{ 'fc-tone-' . ($stat['tone'] ?? 'gray') }}">
                    <p class="fc-stat-label">{{ $stat['label'] }}</p>
                    <p class="fc-stat-value">{{ $stat['value'] }}</p>
                    <p class="fc-stat-description">{{ $stat['description'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="fc-panels">
            <article class="fc-card">
                <h3 class="fc-panel-title">Origem da Receita</h3>

                <div class="fc-list">
                    @forelse ($this->revenueOrigins as $origin)
                        <div class="fc-list-item">
                            <div>
                                <p class="fc-list-title">{{ $origin['source'] }}</p>
                                <p class="fc-list-meta">{{ $origin['transactions'] }} transações</p>
                            </div>
                            <p class="fc-list-value">{{ number_format($origin['amount'], 2, ',', '.') }} MT</p>
                        </div>
                    @empty
                        <p class="fc-empty">Sem receitas concluídas para o período selecionado.</p>
                    @endforelse
                </div>
            </article>

            <article class="fc-card">
                <h3 class="fc-panel-title">Canais de Venda</h3>

                <div class="fc-table-wrap">
                    <table class="fc-table">
                        <thead>
                            <tr>
                                <th>Canal</th>
                                <th>Vendas</th>
                                <th>Qtd. vendida</th>
                                <th class="fc-text-right">Receita</th>
                                <th class="fc-text-right">Ganho</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->salesByChannel as $channel)
                                <tr>
                                    <td>{{ $channel['channel'] }}</td>
                                    <td>{{ $channel['sales'] }}</td>
                                    <td>{{ number_format((float) round($channel['quantity']), 0, ',', '.') }}</td>
                                    <td class="fc-text-right fc-amount-success">{{ number_format($channel['amount'], 2, ',', '.') }} MT</td>
                                    <td class="fc-text-right {{ $channel['profit'] >= 0 ? 'fc-amount-success' : 'fc-amount-danger' }}">{{ number_format($channel['profit'], 2, ',', '.') }} MT</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="color: var(--fc-muted);">Sem vendas registadas no período.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="fc-card">
            <h3 class="fc-panel-title">Transações</h3>

            <div class="fc-table-wrap">
                <table class="fc-table" style="min-width: 900px;">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Utilizador</th>
                            <th>Origem</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th class="fc-text-right">Valor</th>
                            <th>Motivo / descrição</th>
                            <th>Referência</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->transactions as $transaction)
                            <tr>
                                <td>{{ $transaction['date'] }}</td>
                                <td>{{ $transaction['user'] }}</td>
                                <td>{{ $transaction['source'] }}</td>
                                <td>{{ $transaction['type'] }}</td>
                                <td>
                                    <span class="fc-badge {{ $transaction['status_tone'] === 'success' ? 'fc-badge-success' : ($transaction['status_tone'] === 'warning' ? 'fc-badge-warning' : 'fc-badge-gray') }}">
                                        {{ $transaction['status'] }}
                                    </span>
                                </td>
                                <td class="fc-text-right {{ $transaction['amount_tone'] === 'danger' ? 'fc-amount-danger' : 'fc-amount-success' }}">
                                    {{ $transaction['amount'] }}
                                </td>
                                <td>{{ $transaction['reason'] }}</td>
                                <td>{{ $transaction['reference'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="color: var(--fc-muted);">Sem transações para os filtros selecionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
