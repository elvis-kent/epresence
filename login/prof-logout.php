<?php
session_start();
session_unset();
session_destroy();
header('Location: prof_login.php');
exit;
