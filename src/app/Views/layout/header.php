<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理システム</title>
    <link rel="stylesheet" href="<?= base_url('css/output.css') ?>">
</head>

<body class="bg-gray-100">

    <header class="bg-white shadow relative">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex flex-col">
                    <h1 class="text-lg sm:text-xl font-bold text-gray-800">
                        勤怠管理システム
                    </h1>

                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        <span class="font-medium">野口知希</span>
                        <span class="mx-1 text-gray-300">|</span>
                        ID: 20250001
                    </p>
                </div>

                <nav class="hidden md:flex items-center space-x-6 text-sm sm:text-lg">
                    <a href="<?= base_url() ?>" class="text-gray-600 hover:text-gray-900 font-medium">勤怠管理</a>
                    <a href="<?= base_url() ?>" class="text-gray-600 hover:text-gray-900 font-medium">交通費精算</a>

                    <a href="<?= base_url() ?>" class="text-gray-600 hover:text-gray-900 font-medium">管理画面</a>

                    <a href="<?= base_url() ?>" class="text-gray-600 hover:text-gray-900 font-medium">ログアウト</a>
                </nav>

                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" type="button" class="text-gray-600 hover:text-gray-900 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
            <nav class="flex flex-col px-4 py-2 space-y-2">
                <a href="<?= base_url() ?>" class="block py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded font-medium">勤怠管理</a>
                <a href="<?= base_url() ?>" class="block py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded font-medium">交通費精算</a>

                <a href="<?= base_url() ?>" class="block py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded font-medium">管理画面</a>

                <a href="<?= base_url() ?>" class="block py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded font-medium">ログアウト</a>
            </nav>
        </div>
    </header>

    <script>
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>

    <main class="container mx-auto px-4 py-8">