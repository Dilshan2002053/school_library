<?php
require_once __DIR__ . "/includes/auth.php";
session_destroy();
header("Location: /school_library/index.php");
exit;
