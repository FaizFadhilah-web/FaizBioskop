<?php
// Guard: hanya admin yang boleh akses
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
