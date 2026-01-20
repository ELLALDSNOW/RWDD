<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');
session_start();

echo "=== Session Tester ===\n\n";
echo "session_name(): " . session_name() . "\n";
echo "session_id(): " . session_id() . "\n\n";

echo "Cookies sent by browser:\n";
print_r($_COOKIE);
echo "\n";

echo "Session contents:\n";
print_r($_SESSION);
echo "\n";

echo "PHP session settings:\n";
echo "session.save_path = " . ini_get('session.save_path') . "\n";
echo "session.cookie_secure = " . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_httponly = " . ini_get('session.cookie_httponly') . "\n";
echo "session.use_strict_mode = " . ini_get('session.use_strict_mode') . "\n";
echo "\n";

echo "To debug:\n";
echo "- Log in via your login page.\n";
echo "- Then open this page (same host) to confirm PHPSESSID cookie and \$_SESSION['user_id'] exist.\n";