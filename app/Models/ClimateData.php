<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClimateData extends Model
{
    use HasFactory;

    protected $table = 'climate_data';

    protected $fillable = [
        'recorded_at',
        'temperature',
        'source',
        'imported_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'imported_at' => 'datetime',
        'temperature' => 'decimal:2',
    ];

    /**
     * Scope for filtering by source
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('recorded_at', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by source
     */
    public function scopeMinTemperature($query, $minTemp)
    {
        return $query->where('temperature', '>=', $minTemp);
    }

    /**
     * Scope for filtering by source
     */
    public function scopeMaxTemperature($query, $maxTemp)
    {
        return $query->where('temperature', '<=', $maxTemp);
    }
}
