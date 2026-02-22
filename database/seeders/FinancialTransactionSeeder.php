<?php

namespace Database\Seeders;

use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class FinancialTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', 'admin@bakeryapp.test')
            ->first();

        if (! $user) {
            return;
        }

        FinancialTransaction::query()
            ->where('notes', 'like', '[seed]%')
            ->delete();

        FinancialTransaction::query()
            ->whereIn('reference', [
                'SUB-2026-001',
                'SALE-2026-109',
                'CRD-2026-011',
                'CRD-2026-012',
                'EXP-2026-030',
                'EXP-2026-031',
                'SALE-2026-110',
            ])
            ->delete();

        $transactions = [
            [
                'reference' => 'BUY-2026-010-%s',
                'transaction_date' => now()->subDays(10)->toDateString(),
                'type' => FinancialTransaction::TYPE_EXPENSE,
                'status' => FinancialTransaction::STATUS_COMPLETED,
                'source' => FinancialTransaction::SOURCE_PURCHASE,
                'amount' => 980,
                'counterparty' => 'Fornecedor de farinhas e massas',
            ],
            [
                'reference' => 'BUY-2026-011-%s',
                'transaction_date' => now()->subDays(4)->toDateString(),
                'type' => FinancialTransaction::TYPE_EXPENSE,
                'status' => FinancialTransaction::STATUS_COMPLETED,
                'source' => FinancialTransaction::SOURCE_PURCHASE,
                'amount' => 740,
                'counterparty' => 'Fornecedor de frios e laticínios',
            ],
            [
                'reference' => 'LOSS-2026-012-%s',
                'transaction_date' => now()->subDays(7)->toDateString(),
                'type' => FinancialTransaction::TYPE_EXPENSE,
                'status' => FinancialTransaction::STATUS_COMPLETED,
                'source' => FinancialTransaction::SOURCE_LOSS,
                'amount' => 135,
                'counterparty' => 'Quebra de produção de salgados',
            ],
            [
                'reference' => 'LOSS-2026-013-%s',
                'transaction_date' => now()->subDays(3)->toDateString(),
                'type' => FinancialTransaction::TYPE_EXPENSE,
                'status' => FinancialTransaction::STATUS_COMPLETED,
                'source' => FinancialTransaction::SOURCE_LOSS,
                'amount' => 92,
                'counterparty' => 'Produto devolvido (doces)',
            ],
            [
                'reference' => 'OUT-2026-014-%s',
                'transaction_date' => now()->subDays(5)->toDateString(),
                'type' => FinancialTransaction::TYPE_INCOME,
                'status' => FinancialTransaction::STATUS_COMPLETED,
                'source' => FinancialTransaction::SOURCE_OTHER,
                'amount' => 350,
                'counterparty' => 'Workshop presencial',
            ],
            [
                'reference' => 'OUT-2026-015-%s',
                'transaction_date' => now()->subDays(2)->toDateString(),
                'type' => FinancialTransaction::TYPE_EXPENSE,
                'status' => FinancialTransaction::STATUS_PENDING,
                'source' => FinancialTransaction::SOURCE_OTHER,
                'amount' => 210,
                'counterparty' => 'Conta de eletricidade',
            ],
        ];

        $suffix = $user->id;

        foreach ($transactions as $data) {
            $reference = sprintf($data['reference'], $suffix);

            FinancialTransaction::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'reference' => $reference,
                ],
                $data + [
                    'user_id' => $user->id,
                    'reference' => $reference,
                    'amount' => round((float) $data['amount'], 2),
                    'notes' => '[seed] Dados de demonstração de finanças.',
                ]
            );
        }
    }
}
