<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    public const INGREDIENTS = 'ingredients';

    public const RECIPES = 'recipes';

    public const INVENTORY = 'inventory';

    public const PRODUCTION_BATCHES = 'production_batches';

    public const CLIENTS = 'clients';

    public const QUOTES = 'quotes';

    public const ORDERS = 'orders';

    public const SALES = 'sales';

    public const FINANCES = 'finances';

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'feature_plan')
            ->withTimestamps();
    }

    /**
     * @return array<string, array{name: string, description: string}>
     */
    public static function catalog(): array
    {
        return [
            self::INGREDIENTS => [
                'name' => 'Ingredientes',
                'description' => 'Cadastro e gestão de ingredientes e custos.',
            ],
            self::RECIPES => [
                'name' => 'Receitas',
                'description' => 'Precificação, fichas técnicas e rendimento.',
            ],
            self::INVENTORY => [
                'name' => 'Movimentos de stock',
                'description' => 'Entradas, saídas e histórico de inventário.',
            ],
            self::PRODUCTION_BATCHES => [
                'name' => 'Lotes de produção',
                'description' => 'Registo de produção e consumo de ingredientes.',
            ],
            self::CLIENTS => [
                'name' => 'Clientes',
                'description' => 'Cadastro e controlo de clientes.',
            ],
            self::QUOTES => [
                'name' => 'Orçamentos',
                'description' => 'Emissão e gestão de cotações.',
            ],
            self::ORDERS => [
                'name' => 'Pedidos',
                'description' => 'Gestão de pedidos e entregas.',
            ],
            self::SALES => [
                'name' => 'Vendas',
                'description' => 'Registo de vendas online/offline.',
            ],
            self::FINANCES => [
                'name' => 'Finanças',
                'description' => 'Transações financeiras e controlo financeiro.',
            ],
        ];
    }
}
