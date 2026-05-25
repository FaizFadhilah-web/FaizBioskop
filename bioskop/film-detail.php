<?php header('Location: user/film-detail.php' . (isset($_GET['id']) ? '?id='.(int)$_GET['id'] : '')); exit;
