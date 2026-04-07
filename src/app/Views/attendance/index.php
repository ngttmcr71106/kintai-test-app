<?php echo view('layout/header'); ?>

<div class="w-full max-w-6xl mx-auto">
    <?php if (session()->getFlashdata('message')) : ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">
            <p><?= session()->getFlashdata('message') ?></p>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p><?= session()->getFlashdata('error') ?></p>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <ul>
                <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold"><?= esc($displayMonth) ?> 勤怠一覧</h2>

        <div class="flex space-x-2">
            <?php if (isset($isApplied) && $isApplied): ?>
                <button class="bg-gray-400 text-white font-bold py-2 px-4 rounded cursor-not-allowed" disabled>
                    申請済み
                </button>
            <?php else: ?>
                <form action="<?= base_url('attendance/apply') ?>" method="post" onsubmit="return confirm('この月の勤怠を申請しますか？\n申請後は編集できなくなります。');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="month" value="<?= $currentMonth ?>">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        申請する
                    </button>
                </form>
            <?php endif; ?>

            <?php if (empty($isApplied)): ?>
                <button onclick="openNewModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded cursor-pointer">
                    新規登録
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-4 flex space-x-4 text-sm">
        <a href="?month=<?= date('Y-m', strtotime($currentMonth . ' -1 month')) ?>" class="text-indigo-600 hover:underline">&lt; 前月</a>
        <a href="?month=<?= date('Y-m', strtotime($currentMonth . ' +1 month')) ?>" class="text-indigo-600 hover:underline">翌月 &gt;</a>
    </div>

    <div class="bg-white shadow-md rounded overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">勤務日</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">出勤</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">退勤</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">休憩</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">稼働時間</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">有給</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">代休</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">備考</th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">操作</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                // --- 集計ロジック ---
                $totalWorkSeconds = 0;
                $totalPaidSeconds = 0;
                $totalCompSeconds = 0;
                $workDays = 0;

                // 時間計算ヘルパー関数
                if (!function_exists('timeToSeconds')) {
                    function timeToSeconds($time)
                    {
                        if (empty($time) || $time === '-') return 0;
                        $parts = explode(':', $time);
                        return ((int)($parts[0] ?? 0) * 3600) + ((int)($parts[1] ?? 0) * 60);
                    }
                }
                if (!function_exists('secondsToTime')) {
                    function secondsToTime($seconds)
                    {
                        $hours = floor($seconds / 3600);
                        $minutes = floor(($seconds % 3600) / 60);
                        return sprintf('%02d:%02d', $hours, $minutes);
                    }
                }
                ?>

                <?php if (empty($attendances)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                            勤怠データがありません。
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($attendances as $row): ?>
                        <?php
                        // データ整形
                        $start = empty($row['start_time']) ? '-' : substr($row['start_time'], 0, 5);
                        $end   = empty($row['end_time'])   ? '-' : substr($row['end_time'], 0, 5);
                        $break = empty($row['break_time']) ? '-' : substr($row['break_time'], 0, 5);
                        $paid  = empty($row['paid_leave_time']) ? '-' : substr($row['paid_leave_time'], 0, 5);
                        $comp  = empty($row['comp_leave_time']) ? '-' : substr($row['comp_leave_time'], 0, 5);
                        $workTime = $row['work_time_calculated'] ?? '-';

                        // 合計加算
                        if ($start !== '-') $workDays++;
                        $totalWorkSeconds += timeToSeconds($workTime);
                        $totalPaidSeconds += timeToSeconds($paid);
                        $totalCompSeconds += timeToSeconds($comp);
                        ?>

                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?= date('n月j日', strtotime($row['work_date'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= esc($start) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= esc($end) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= esc($break) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800"><?= esc($workTime) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-blue-600 font-bold"><?= esc($paid) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-green-600 font-bold"><?= esc($comp) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs">
                                <?= esc($row['remarks']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button type="button"
                                    onclick='openEditModal(<?= json_encode($row) ?>)'
                                    class="text-indigo-600 hover:text-indigo-900 focus:outline-none cursor-pointer">
                                    編集
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <tr class="bg-gray-100 border-t-2 border-gray-300">
                    <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800">
                        稼働: <?= $workDays ?>日
                    </td>
                    <td class="px-6 py-4" colspan="3"></td>
                    <td class="px-6 py-4 whitespace-nowrap font-bold">
                        <?= secondsToTime($totalWorkSeconds) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-bold text-blue-800">
                        <?= secondsToTime($totalPaidSeconds) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap font-bold text-green-800">
                        <?= secondsToTime($totalCompSeconds) ?>
                    </td>
                    <td class="px-6 py-4" colspan="2"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="attendance-modal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeModal()">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div id="modal-content" class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <?php echo view('attendance/form'); ?>
        </div>

    </div>
</div>

<script>
    const modal = document.getElementById('attendance-modal');
    const formTitle = document.getElementById('modal-title');

    // フォーム要素の取得 (form.php内のIDと一致させる必要があります)
    const inputId = document.querySelector('input[name="id"]');
    const inputDate = document.getElementById('work_date');
    const inputStart = document.getElementById('start_time');
    const inputEnd = document.getElementById('end_time');
    const inputBreak = document.getElementById('break_time');
    const inputPaid = document.getElementById('paid_leave_time');
    const inputComp = document.getElementById('comp_leave_time');
    const inputRemarks = document.getElementById('remarks');

    // 共通: モーダルを開く
    function openModal() {
        modal.classList.remove('hidden');
    }

    // 共通: モーダルを閉じる
    function closeModal() {
        modal.classList.add('hidden');
    }

    // 新規登録用
    function openNewModal() {
        // IDを空にする (新規扱い)
        if (inputId) inputId.value = '';

        // フォームのリセット
        const form = modal.querySelector('form');
        if (form) form.reset();

        // 今日の日付をセット (YYYY-MM-DD)
        const today = new Date().toISOString().split('T')[0];
        if (inputDate) inputDate.value = today;

        // デフォルト値
        if (inputBreak) inputBreak.value = '01:00:00';

        if (formTitle) formTitle.innerText = '勤怠 新規登録';
        openModal();
    }

    // 編集用
    function openEditModal(data) {
        // データの埋め込み
        if (inputId) inputId.value = data.id;

        if (inputDate) inputDate.value = data.work_date;

        // 時間系 (NULLの場合は空文字にする)
        if (inputStart) inputStart.value = data.start_time || '';
        if (inputEnd) inputEnd.value = data.end_time || '';
        if (inputBreak) inputBreak.value = data.break_time || '01:00:00';

        if (inputPaid) inputPaid.value = data.paid_leave_time || '';
        if (inputComp) inputComp.value = data.comp_leave_time || '';

        if (inputRemarks) inputRemarks.value = data.remarks || '';

        if (formTitle) formTitle.innerText = '勤怠 編集';
        openModal();
    }
</script>

<?php echo view('layout/footer'); ?>