<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApplicationsTables extends Migration
{
    public function up()
    {
        // --- 勤怠申請管理テーブル ---
        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'target_year' => ['type' => 'INT', 'constraint' => 4],
            'target_month' => ['type' => 'INT', 'constraint' => 2],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'applied'], // applied:申請済み
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // 同じユーザーが同じ月を重複して申請できないように複合ユニーク制約
        $this->forge->addUniqueKey(['user_id', 'target_year', 'target_month']);
        $this->forge->createTable('attendance_applications');

        // --- 交通費申請管理テーブル（交通費申請機能を削除したためコメントアウト）---
        // $this->forge->addField([
        //     'id' => ['type' => 'INT', 'auto_increment' => true, 'unsigned' => true],
        //     'user_id' => ['type' => 'INT', 'unsigned' => true],
        //     'target_year' => ['type' => 'INT', 'constraint' => 4],
        //     'target_month' => ['type' => 'INT', 'constraint' => 2],
        //     'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'applied'],
        //     'created_at' => ['type' => 'DATETIME', 'null' => true],
        //     'updated_at' => ['type' => 'DATETIME', 'null' => true],
        // ]);
        // $this->forge->addKey('id', true);
        // $this->forge->addUniqueKey(['user_id', 'target_year', 'target_month']);
        // $this->forge->createTable('expense_applications');
    }

    public function down()
    {
        $this->forge->dropTable('attendance_applications');
        // $this->forge->dropTable('expense_applications');
    }
}