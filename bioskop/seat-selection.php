<?php header('Location: user/seat-selection.php' . (isset($_GET['jadwal_id']) ? '?jadwal_id='.(int)$_GET['jadwal_id'] : '')); exit;
