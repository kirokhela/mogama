<?php
require_once 'includes/auth.php';
do_logout();
header('Location: login.php');
exit;