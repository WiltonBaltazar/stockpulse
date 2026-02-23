<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()
            ->where('email', 'admin@bakeryapp.test')
            ->first();

        if (! $user) {
            return;
        }

        Client::query()
            ->where('user_id', $user->id)
            ->where('notes', 'like', '[seed]%')
            ->delete();

        $clients = [
            [
                'name' => 'Café Central Maputo',
                'contact_number' => '+258841101010',
                'email' => 'compras@cafecentral.mz',
                'document_number' => 'NUIT 400110100',
                'address' => 'Av. Julius Nyerere, Maputo',
                'is_active' => true,
            ],
            [
                'name' => 'Cantina Horizonte',
                'contact_number' => '+258842202020',
                'email' => 'cantina@horizonte.mz',
                'document_number' => 'NUIT 400220200',
                'address' => 'Bairro Triunfo, Maputo',
                'is_active' => true,
            ],
            [
                'name' => 'Eventos MZ',
                'contact_number' => '+258843303030',
                'email' => 'pedidos@eventosmz.co.mz',
                'document_number' => 'NUIT 400330300',
                'address' => 'Rua dos Trabalhadores, Matola',
                'is_active' => true,
            ],
            [
                'name' => 'Empresa Sol Nascente',
                'contact_number' => '+258844404040',
                'email' => 'rh@solnascente.mz',
                'document_number' => 'NUIT 400440400',
                'address' => 'Av. Marginal, Maputo',
                'is_active' => true,
            ],
            [
                'name' => 'Cliente Walk-in',
                'contact_number' => null,
                'email' => null,
                'document_number' => null,
                'address' => 'Balcão da loja',
                'is_active' => true,
            ],
            [
                'name' => 'Parceria Bairro Azul',
                'contact_number' => '+258845505050',
                'email' => 'parceria@bairroazul.mz',
                'document_number' => 'NUIT 400550500',
                'address' => 'Bairro Azul, Maputo',
                'is_active' => false,
            ],
        ];

        foreach ($clients as $data) {
            Client::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $data['name'],
                ],
                $data + [
                    'user_id' => $user->id,
                    'notes' => '[seed] Cliente de demonstração para vendas e controlo.',
                ]
            );
        }
    }
}
