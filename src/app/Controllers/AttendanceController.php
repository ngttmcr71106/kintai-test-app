<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Models\AttendanceApplicationModel;

class AttendanceController extends BaseController
{
    protected $attendanceModel;

    public function __construct()
    {
        $this->attendanceModel = new AttendanceModel();
    }

    /**
     * 勤怠一覧画面表示
     */
    public function index()
    {
        $userId = session('user_id');

        $currentMonth = $this->request->getVar('month') ?? date('Y-m');
        $displayMonth = date('Y年n月', strtotime($currentMonth . '-01'));

        $attendances = $this->attendanceModel
                            ->where('user_id', $userId)
                            ->like('work_date', $currentMonth, 'after')
                            ->orderBy('work_date', 'ASC')
                            ->findAll();

        foreach ($attendances as &$row) {
            $row['work_time_calculated'] = $this->calculateWorkTime(
                $row['start_time'],
                $row['end_time'],
                $row['break_time']
            );
        }

        list($year, $month) = explode('-', $currentMonth);
        $appModel = new AttendanceApplicationModel();

        $application = $appModel->where([
            'user_id'      => $userId,
            'target_year'  => $year,
            'target_month' => $month
        ])->first();

        $isApplied = !empty($application);

        $data = [
            'attendances'  => $attendances,
            'displayMonth' => $displayMonth,
            'currentMonth' => $currentMonth,
            'isApplied'    => $isApplied,
        ];

        return view('attendance/index', $data);
    }

    /**
     * 保存処理（新規・編集共通）
     */
    public function save()
    {
        $userId = session('user_id');

        $rules = [
            'work_date' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $postData = [
            'id'              => $this->request->getPost('id'),
            'user_id'         => $userId,
            'work_date'       => $this->request->getPost('work_date'),
            'start_time'      => $this->request->getPost('start_time') ?: null,
            'end_time'        => $this->request->getPost('end_time') ?: null,
            'break_time'      => $this->request->getPost('break_time') ?: '01:00:00',
            'paid_leave_time' => $this->request->getPost('paid_leave_time') ?: null,
            'comp_leave_time' => $this->request->getPost('comp_leave_time') ?: null,
            'remarks'         => $this->request->getPost('remarks'),
        ];

        if ($this->attendanceModel->save($postData)) {
            return redirect()->to(base_url())->with('message', '勤怠データを保存しました。');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->attendanceModel->errors());
        }
    }

    /**
     * 申請処理
     */
    public function apply()
    {
        $userId = session('user_id');
        $monthStr = $this->request->getPost('month');

        if (!$monthStr) {
            return redirect()->back()->with('error', '月が指定されていません');
        }

        list($year, $month) = explode('-', $monthStr);

        $appModel = new AttendanceApplicationModel();

        $exists = $appModel->where([
            'user_id'      => $userId,
            'target_year'  => $year,
            'target_month' => $month
        ])->first();

        if ($exists) {
            return redirect()->back()->with('error', '既に申請済みです');
        }

        $appModel->save([
            'user_id'      => $userId,
            'target_year'  => $year,
            'target_month' => $month,
            'status'       => 'applied'
        ]);

        return redirect()->to(base_url())->with('message', '勤怠を申請しました！');
    }

    /**
     * [private] 稼働時間の計算 (H:i 形式で返す)
     */
    private function calculateWorkTime($start, $end, $break)
    {
        if (empty($start) || empty($end)) {
            return '-';
        }
        $startTime = strtotime($start);
        $endTime   = strtotime($end);

        $breakSeconds = 0;
        if (!empty($break)) {
            $parts = explode(':', $break);
            $h = isset($parts[0]) ? (int)$parts[0] : 0;
            $m = isset($parts[1]) ? (int)$parts[1] : 0;
            $s = isset($parts[2]) ? (int)$parts[2] : 0;
            $breakSeconds = ($h * 3600) + ($m * 60) + $s;
        }

        $diffSeconds = ($endTime - $startTime) - $breakSeconds;
        if ($diffSeconds < 0) return 'エラー';

        $hours   = floor($diffSeconds / 3600);
        $minutes = floor(($diffSeconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
