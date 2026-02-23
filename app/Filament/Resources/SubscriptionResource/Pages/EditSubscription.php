<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Concerns\RedirectsToResourceIndex;
use App\Filament\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Models\Subscription;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditSubscription extends EditRecord
{
    use RedirectsToResourceIndex;

    protected static string $resource = SubscriptionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->normalizeData($data);
    }

    protected function afterSave(): void
    {
        $this->ensureSingleActiveSubscription();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeData(array $data): array
    {
        $plan = filled($data['plan_id'] ?? null)
            ? Plan::query()->find((int) $data['plan_id'])
            : null;

        if ($plan) {
            if (blank($data['price'] ?? null)) {
                $data['price'] = (float) $plan->price;
            }

            if (blank($data['currency'] ?? null)) {
                $data['currency'] = (string) $plan->currency;
            }
        }

        $startedAt = filled($data['started_at'] ?? null)
            ? Carbon::parse((string) $data['started_at'])
            : ($this->record->started_at ?: now());
        $data['started_at'] = $startedAt;

        if (blank($data['ends_at'] ?? null) && $plan) {
            $data['ends_at'] = $startedAt
                ->copy()
                ->addMonthsNoOverflow(max((int) $plan->duration_months, 1));
        }

        if (($data['status'] ?? Subscription::STATUS_ACTIVE) !== Subscription::STATUS_ACTIVE && blank($data['ends_at'] ?? null)) {
            $data['ends_at'] = now();
        }

        $data['price'] = round((float) ($data['price'] ?? 0), 2);
        $data['currency'] = trim((string) ($data['currency'] ?? 'MT')) ?: 'MT';

        return $data;
    }

    private function ensureSingleActiveSubscription(): void
    {
        if ((string) $this->record->status !== Subscription::STATUS_ACTIVE) {
            return;
        }

        Subscription::query()
            ->where('user_id', $this->record->user_id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereKeyNot($this->record->id)
            ->update([
                'status' => Subscription::STATUS_CANCELLED,
                'ends_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
