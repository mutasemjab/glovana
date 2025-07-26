<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FineSetting extends Model
{
    use HasFactory;
    
      protected $fillable = [
        'key',
        'value',
        'description'
    ];

    // Helper methods to get settings
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value, $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }

    // Get all settings as key-value array
    public static function getAllSettings()
    {
        return self::pluck('value', 'key')->toArray();
    }

    // Default settings
    public static function getDefaultSettings()
    {
        return [
            'late_cancellation_hours' => [
                'value' => '24',
                'description' => 'Hours before appointment when cancellation becomes subject to fine'
            ],
            'fine_percentage' => [
                'value' => '25',
                'description' => 'Percentage of appointment total to charge as fine (%)'
            ],
            'minimum_fine_amount' => [
                'value' => '5',
                'description' => 'Minimum fine amount regardless of percentage'
            ],
            'maximum_fine_amount' => [
                'value' => '100',
                'description' => 'Maximum fine amount regardless of percentage'
            ],
            'auto_apply_fines' => [
                'value' => '1',
                'description' => 'Automatically apply fines (1=yes, 2=no)'
            ],
            'allow_negative_balance' => [
                'value' => '2',
                'description' => 'Allow user balance to go negative (1=yes, 2=no)'
            ]
        ];
    }

    // Seed default settings
    public static function seedDefaults()
    {
        $defaults = self::getDefaultSettings();
        
        foreach ($defaults as $key => $data) {
            self::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $data['value'],
                    'description' => $data['description']
                ]
            );
        }
    }
}
