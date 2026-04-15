<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CarRegistry extends Model
{
    use HasFactory;

    protected $fillable = [
        'parking_lot_id',
        'plate_number',
        'entry_time',
        'exit_time',
    ];

    protected $casts = [
        'entry_time' => 'datetime',
        'exit_time' => 'datetime',
    ];

    public function parkingLot(): BelongsTo
    {
        return $this->belongsTo(ParkingLot::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('exit_time')
                     ->orWhere('exit_time', '>', Carbon::now());
    }
}
?>

