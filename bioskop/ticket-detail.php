<?php header('Location: user/ticket-detail.php' . (isset($_GET['id']) ? '?id='.(int)$_GET['id'] : '')); exit;
