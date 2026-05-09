<?php
/**
 * MoeHome 后台管理 - 登出处理
 */

declare(strict_types=1);

session_start();
require_once __DIR__ . '/api/database.php';

logout();

header('Location: login.php?loggedout=1');
exit;
