<?php

if (!User::isAdmin()) {
    return;
}

// Проверка наличия не установленных модулей
$blocks = BlockManager::fetch();
$FSBlocks = BlockManager::getBlocksInFS();
$installedBlocks = [];
foreach ($blocks as $block) {
    $installedBlocks[$block->ident] = true;
}

$system_messages = [];

foreach ($FSBlocks as $ident) {
    if (!isset($installedBlocks[$ident])) {
        $msg = 'Обнаружен новый модуль <b>"' . $ident . '"</b> в системе&nbsp;&nbsp;';
        $InstallFilePath = Cfg::DIR_MODULES . $ident . Cfg::DS . 'install.php';
        if (FsFile::isExists($InstallFilePath)) {
            $msg .= '<a class="sensei-btn sensei-btn_m sensei-btn_blue js__module-install" data-module-ident="' . $ident . '" href="#">УСТАНОВИТЬ</a>';
        } else {
            $msg .= '( Файл установки отсуствует )';
        }
        $system_messages[] = $msg;
    }
}

Application::assign('system_messages', $system_messages);
Application::showContent('admin', 'system_notifications');
