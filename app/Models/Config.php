<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @mixin Eloquent
 */
class Config extends Model
{
    protected $table = 'config';
    protected $primaryKey = 'config';

    public function getConfigAttribute($value)
    {
        $value = json_decode($value, true);

        $config = json_encode([
            'chips' => Arr::get($value, 'chips') ?? 10000,
            'commission' => Arr::get($value, 'commission') ?? 2,
            'keep_completed' => Arr::get($value, 'keep_completed') ?? 1,
            'providers' => Arr::get($value, 'providers') ?? ['testdata'],
        ]);

        return json_decode($config, true);
    }
}
