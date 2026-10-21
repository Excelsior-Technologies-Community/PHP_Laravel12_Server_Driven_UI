<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UITelemetry extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'ui_telemetry';

    protected $fillable = [
        'component_id',
        'screen',
        'event_type',
        'platform',
        'variant',
        'visitor_id',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'component_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function component()
    {
        return $this->belongsTo(UIComponent::class, 'component_id');
    }
}
