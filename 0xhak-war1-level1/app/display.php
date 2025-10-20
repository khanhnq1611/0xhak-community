<?php
$file = $_GET['file'] ?? '';
if (empty($file)) {
    http_response_code(404);
    exit;
}
// Vulnerable: using include() instead of readfile()
include(__DIR__ . '/uploads/avatars/' . $file);
?>