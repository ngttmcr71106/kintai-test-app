<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'employee_id'   => 20250001,
                'email'         => 'admin@example.com',
                'password'      => password_hash('admin123', PASSWORD_DEFAULT),
                'department_id' => 1,
                'role_flag'     => 1, // 管理者
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'employee_id'   => 20250002,
                'email'         => 'user01@example.com',
                'password'      => password_hash('user123', PASSWORD_DEFAULT),
                'department_id' => 1,
                'role_flag'     => 0, // 一般社員
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'employee_id'   => 20250003,
                'email'         => 'user02@example.com',
                'password'      => password_hash('user123', PASSWORD_DEFAULT),
                'department_id' => 2,
                'role_flag'     => 0, // 一般社員
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        // usersテーブルにデータを一括挿入
        $this->db->table('users')->insertBatch($data);
    }
}
