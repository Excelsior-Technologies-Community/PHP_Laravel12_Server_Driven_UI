<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UIExperiment extends Model
{
    use HasFactory;

    protected $table = 'ui_experiments';

    protected $fillable = [
        'name',
        'screen',
        'target_component_id',
        'traffic_percent',
        'variant_name',
        'variant_properties',
        'status',
        'goal_action',
    ];

    protected $casts = [
        'traffic_percent' => 'integer',
        'variant_properties' => 'array',
        'target_component_id' => 'integer',
    ];

    public function targetComponent()
    {
        return $this->belongsTo(UIComponent::class, 'target_component_id');
    }

    public function telemetry()
    {
        return $this->hasMany(UITelemetry::class, 'component_id', 'target_component_id');
    }

    /**
     * Determine if a visitor should see Variant B using deterministic sticky hashing.
     */
    public function shouldAssignVariant(string $visitorId): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->traffic_percent <= 0) {
            return false;
        }

        if ($this->traffic_percent >= 100) {
            return true;
        }

        $hash = crc32($visitorId . '_exp_' . $this->id);
        $bucket = abs($hash) % 100;

        return $bucket < $this->traffic_percent;
    }
}
