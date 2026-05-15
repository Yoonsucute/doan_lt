<?php

require_once dirname(__DIR__) . '/config.php';

session_destroy();

header('Location: ' . base_url('auth/login.php'));

?>
