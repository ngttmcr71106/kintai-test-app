<!-- このファイルは attendance/index.php のモーダル内に読み込まれることを想定しています -->

<!-- 勤怠登録・編集フォーム -->
<form action="<?= base_url('attendance/save') ?>" method="post" class="p-6">
    <?= csrf_field() ?>

    <input type="hidden" name="id" value="<?= isset($attendance['id']) ? $attendance['id'] : '' ?>">

    <div class="mb-6 text-center">
        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
            勤怠 登録/編集
        </h3>
    </div>

    <?php if (session()->has('errors')) : ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            <ul>
                <?php foreach (session('errors') as $error) : ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <div class="mb-4">
        <label for="work_date" class="block text-gray-700 text-sm font-bold mb-2">勤務日 <span class="text-red-500">*</span></label>
        <input type="date" name="work_date" id="work_date" required
            value="<?= esc($attendance['work_date'] ?? date('Y-m-d')) ?>"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500">
    </div>

    <div class="flex flex-wrap -mx-3 mb-4">
        <div class="w-full md:w-1/2 px-3 mb-4 md:mb-0">
            <label for="start_time" class="block text-gray-700 text-sm font-bold mb-2">出勤時間</label>
            <input type="time" name="start_time" id="start_time"
                value="<?= esc($attendance['start_time'] ?? '09:00') ?>"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500"
                required
            >
        </div>
        <div class="w-full md:w-1/2 px-3">
            <label for="end_time" class="block text-gray-700 text-sm font-bold mb-2">退勤時間</label>
            <input type="time" name="end_time" id="end_time"
                value="<?= esc($attendance['end_time'] ?? '18:00') ?>"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500"
                required
            >
        </div>
    </div>

    <div class="mb-4">
        <label for="break_time" class="block text-gray-700 text-sm font-bold mb-2">休憩時間</label>
        <input type="time" name="break_time" id="break_time"
            value="<?= esc($attendance['break_time'] ?? '01:00') ?>"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500">
    </div>

    <hr class="my-6 border-gray-200">

    <div class="flex flex-wrap -mx-3 mb-4">
        <div class="w-full md:w-1/2 px-3 mb-4 md:mb-0">
            <label for="paid_leave_time" class="block text-gray-700 text-sm font-bold mb-2">有給休暇</label>
            <select name="paid_leave_time" id="paid_leave_time" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 bg-white">
                <option value="">未使用</option>
                <option value="04:00:00" <?= (isset($attendance['paid_leave_time']) && $attendance['paid_leave_time'] == '04:00:00') ? 'selected' : '' ?>>半休 (4.0h)</option>
                <option value="08:00:00" <?= (isset($attendance['paid_leave_time']) && $attendance['paid_leave_time'] == '08:00:00') ? 'selected' : '' ?>>全休 (8.0h)</option>
            </select>
        </div>
        <div class="w-full md:w-1/2 px-3">
            <label for="comp_leave_time" class="block text-gray-700 text-sm font-bold mb-2">代休</label>
            <select name="comp_leave_time" id="comp_leave_time" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500 bg-white">
                <option value="">未使用</option>
                <option value="04:00:00" <?= (isset($attendance['comp_leave_time']) && $attendance['comp_leave_time'] == '04:00:00') ? 'selected' : '' ?>>半休 (4.0h)</option>
                <option value="08:00:00" <?= (isset($attendance['comp_leave_time']) && $attendance['comp_leave_time'] == '08:00:00') ? 'selected' : '' ?>>全休 (8.0h)</option>
            </select>
        </div>
    </div>

    <div class="mb-6">
        <label for="remarks" class="block text-gray-700 text-sm font-bold mb-2">備考</label>
        <textarea name="remarks" id="remarks" rows="3" placeholder="遅刻理由など"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-indigo-500"><?= esc($attendance['remarks'] ?? '') ?></textarea>
    </div>

    <div class="flex items-center justify-between">
        <?php if (isset($attendance['id']) && !empty($attendance['id'])): ?>
            <a href="<?= base_url('attendance/delete/' . $attendance['id']) ?>"
                onclick="return confirm('本当に削除しますか？')"
                class="text-red-500 hover:text-red-700 text-sm font-bold">
                削除
            </a>
        <?php else: ?>
            <div></div> <?php endif; ?>

        <div class="flex">
            <button type="button" onclick="closeModal()" class="mr-4 text-gray-500 hover:text-gray-700 font-bold py-2 px-4 cursor-pointer">
                キャンセル
            </button>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline cursor-pointer">
                保存
            </button>
        </div>
    </div>
</form>