<?php

declare(strict_types=1);

/**
 * KOD ÖRNEĞİ — Müşteri kredi limiti / risk maruziyeti kontrolü.
 *
 * B2B'de bayi açık hesapla alışveriş yapar. Yeni sipariş kabul edilmeden önce toplam
 * maruziyet (mevcut bakiye + açık siparişler + yeni sipariş) kredi limitiyle kıyaslanır.
 * Limit aşılırsa sipariş `pending_approval` durumuna düşürülür (UI tarafında satış
 * müdürü onayı beklenir).
 *
 * Tüm hesaplar bcmath ile (parada float yok). Limit NULL/0 ise sınırsız kabul edilir.
 */

namespace App\Modules\Order\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Modules\Order\Enums\OrderStatus;

final class CreditLimitChecker
{
    private const SCALE = 2;

    /** @return array{ok: bool, limit: string, usage: string, requested: string} */
    public function check(Customer $customer, string $orderTotal): array
    {
        $limit = (string) ($customer->getAttribute('credit_limit') ?? '0');

        // NULL veya 0 = sınırsız → kontrol geçilir
        if (! is_numeric($limit) || bccomp($limit, '0', self::SCALE) <= 0) {
            return ['ok' => true, 'limit' => '0.00', 'usage' => '0.00', 'requested' => $orderTotal];
        }

        $currentBalance = (string) ($customer->getAttribute('current_balance') ?? '0');
        if (! is_numeric($currentBalance)) {
            $currentBalance = '0';
        }

        // "Açık sipariş": henüz teslim edilmemiş / iptal olmamış siparişlerin toplamı
        $openOrdersTotal = (string) Order::query()
            ->where('customer_id', $customer->getKey())
            ->whereIn('status', [
                OrderStatus::PendingApproval->value,
                OrderStatus::Approved->value,
                OrderStatus::Preparing->value,
                OrderStatus::ReadyToShip->value,
            ])
            ->sum('total');

        if (! is_numeric($openOrdersTotal)) {
            $openOrdersTotal = '0';
        }

        $usage = bcadd($currentBalance, $openOrdersTotal, self::SCALE);
        $totalExposure = bcadd($usage, $orderTotal, self::SCALE);

        return [
            'ok'        => bccomp($totalExposure, $limit, self::SCALE) <= 0,
            'limit'     => bcadd($limit, '0', self::SCALE),
            'usage'     => bcadd($usage, '0', self::SCALE),
            'requested' => bcadd($orderTotal, '0', self::SCALE),
        ];
    }
}
