<?php
require_once '../config/database.php';

// Hapus session admin saja, biarkan session user tetap
unset($_SESSION['admin_id']);
unset($_SESSION['admin_nama']);
unset($_SESSION['admin_username']);

header('Location: login.php');
exit;
