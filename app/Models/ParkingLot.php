<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ParkingLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'total_capacity',
        'price_per_hour',
        'pricing_rules',
        'latitude',
        'longitude',
        'working_hours',
        'is_active',
        'image',
    ];

    protected $casts = [
        'latitude'       => 'decimal:8',
        'longitude'      => 'decimal:8',
        'price_per_hour' => 'decimal:2',
        'total_capacity' => 'integer',
        'is_active'      => 'boolean',
        'pricing_rules'  => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithStatus($query)
    {
        return $query->withCount([
            'carRegistries as active_registries_count' => function ($q) {
                $q->active();
            },
            'bookings as active_bookings_count' => function ($q) {
                $q->where('status', 'active');
            }
        ]);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function carRegistries(): HasMany
    {
        return $this->hasMany(CarRegistry::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                     ->orWhere('address', 'like', "%{$search}%");
    }

    public function getAvailableSpacesAttribute()
    {
        $activeBookings = $this->bookings()->where('status', 'active')->count();
        $activeRegistries = $this->carRegistries()->active()->count();
        $occupied = $activeBookings + $activeRegistries;
        return max(0, $this->total_capacity - $occupied);
    }

    public function getOccupiedSpacesAttribute()
    {
        $activeBookings = $this->bookings()->where('status', 'active')->count();
        $activeRegistries = $this->carRegistries()->active()->count();
        return $activeBookings + $activeRegistries;
    }

    /**
     * Build the pricing snapshot array to store on a booking at creation time.
     * Format: ['base' => float, 'rules' => [1 => float, ..., 7 => float]]
     */
    public function pricingSnapshot(): array
    {
        return [
            'base'  => (float) $this->price_per_hour,
            'rules' => $this->pricing_rules ?? [],
        ];
    }

    /**
     * Calculate fee and per-day breakdown between two timestamps.
     *
     * Pass a $snapshot (from booking->pricing_snapshot) to use the rates that were
     * in effect when the booking was created, guaranteeing price changes never
     * retroactively affect historical or in-progress bookings.
     *
     * Returns ['total' => float, 'details' => [['day','date','hours','rate','subtotal'], ...]]
     */
    public function calculateFee(Carbon $start, Carbon $end, ?array $snapshot = null): array
    {
        $base    = $snapshot ? (float) ($snapshot['base'] ?? $this->price_per_hour)
                             : (float) $this->price_per_hour;
        $rules   = $snapshot ? ($snapshot['rules'] ?? [])
                             : ($this->pricing_rules ?? []);
        $details = [];
        $total   = 0.0;
        $cursor  = $start->copy()->seconds(0);

        while ($cursor < $end) {
            $dayEnd = $cursor->copy()->endOfDay()->addSecond();
            $segEnd = ($dayEnd < $end) ? $dayEnd : $end;

            $dow      = (int) $cursor->format('N'); // 1=Mon … 7=Sun
            $rate     = isset($rules[$dow]) ? (float) $rules[$dow] : $base;
            $hours    = round($cursor->diffInMinutes($segEnd) / 60, 4);
            $subtotal = $hours * $rate;

            $details[] = [
                'day'      => $this->arabicDayName($dow),
                'date'     => $cursor->format('Y/m/d'),
                'hours'    => round($hours, 2),
                'rate'     => $rate,
                'subtotal' => round($subtotal, 2),
            ];

            $total  += $subtotal;
            $cursor  = $segEnd;
        }

        return ['total' => (float) number_format(ceil($total), 2, '.', ''), 'details' => $details];
    }

    private function arabicDayName(int $iso): string
    {
        return ['الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت', 'الأحد'][$iso - 1] ?? '';
    }

    public function getUsagePercentageAttribute()
    {
        $occupied = $this->occupied_spaces;
        return $this->total_capacity > 0 ? round(($occupied / $this->total_capacity) * 100, 2) : 0;
    }
}
?>
