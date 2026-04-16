<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parking_lot_id',
        'customer_name',
        'phone',
        'vehicle_plate',
        'source',
        'start_time',
        'end_time',
        'status',
        'total_fee',
        'payment_method',
        'payment_proof',
        'paid_at',
        'pricing_snapshot',
        'notes',
    ];

    protected $casts = [
        'start_time'  => 'datetime',
        'end_time'    => 'datetime',
        'paid_at'     => 'datetime',
        'total_fee'        => 'decimal:2',
        'pricing_snapshot' => 'array',
    ];

    public function parkingLot(): BelongsTo
    {
        return $this->belongsTo(ParkingLot::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
?>

