<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendancesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'work_date' => [ // 勤務日
                'type' => 'DATE',
            ],
            'start_time' => [ // 出勤
                'type' => 'TIME',
                'null' => true,
            ],
            'end_time' => [ // 退勤
                'type' => 'TIME',
                'null' => true,
            ],
            'break_time' => [ // 休憩
                'type'    => 'TIME',
                'default' => '01:00:00', // デフォルト1時間
            ],
            'paid_leave_time' => [ // 有給（04:00:00 or 08:00:00）
                'type' => 'TIME',
                'null' => true,
            ],
            'comp_leave_time' => [ // 代休
                'type' => 'TIME',
                'null' => true,
            ],
            'remarks' => [ // 備考
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE'); // usersテーブルがある前提
        $this->forge->createTable('attendances');
    }

    public function down()
    {
        $this->forge->dropTable('attendances');
    }
}