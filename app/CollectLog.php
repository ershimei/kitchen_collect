<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CollectLog extends Model
{
    protected $table = 'collect_resource_log';

    protected $fillable = [
        'file_path',
        'file_name',
        'file_dir',
        'file_time',
        'file_status'
    ];

    /**
     * 获取最后采集的一条
     */
    public static function getLastRow()
    {
        return self::query()->orderBy('id', 'desc')->first()->toArray();
    }
}
