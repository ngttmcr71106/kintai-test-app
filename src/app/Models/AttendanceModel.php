<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table            = 'attendances';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // 配列で取得

    // 保存を許可するカラム（ここに追加しないと保存されません！）
    protected $allowedFields    = [
        'user_id',
        'work_date',
        'start_time',
        'end_time',
        'break_time',
        'paid_leave_time', // 有給
        'comp_leave_time', // 代休
        'remarks'
    ];

    // 日付の自動更新
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}