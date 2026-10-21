<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UIComponent extends Model
{
    use HasFactory;

    protected $table = 'ui_components';

    protected $fillable = [
        'type',
        'name',
        'properties',
        'screen',
        'order',
        'is_active',
        'platforms',
        'min_version',
        'max_version',
        'variant',
        'experiment_id',
    ];

    protected $casts = [
        'properties' => 'array',
        'platforms' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
        'experiment_id' => 'integer',
    ];

    /**
     * Scope a query to only include active components.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include components for a specific screen.
     */
    public function scopeForScreen($query, $screen)
    {
        return $query->where('screen', $screen);
    }

    /**
     * Scope a query to filter by target client platform (ios, android, web).
     */
    public function scopeForPlatform($query, ?string $platform)
    {
        if (!$platform || $platform === 'all') {
            return $query;
        }

        return $query->where(function ($q) use ($platform) {
            $q->whereNull('platforms')
              ->orWhereJsonContains('platforms', 'all')
              ->orWhereJsonContains('platforms', $platform);
        });
    }

    /**
     * Scope a query to order components.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    /**
     * Telemetry relation
     */
    public function telemetry()
    {
        return $this->hasMany(UITelemetry::class, 'component_id');
    }

    /**
     * Experiment relation
     */
    public function experiment()
    {
        return $this->belongsTo(UIExperiment::class, 'experiment_id');
    }
}
