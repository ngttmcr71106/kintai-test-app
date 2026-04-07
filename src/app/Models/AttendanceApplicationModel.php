<?php
namespace App\Models;
use CodeIgniter\Model;

class AttendanceApplicationModel extends Model
{
    protected $table = 'attendance_applications';
    protected $allowedFields = ['user_id', 'target_year', 'target_month', 'status'];
    protected $useTimestamps = true;
}