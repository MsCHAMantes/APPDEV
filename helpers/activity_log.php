<?php
function logActivity($con, $user_id, $role, $action) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

        $stmt = $con->prepare("
            INSERT INTO activity_logs (user_id, role, action, ip_address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $role, $action, $ip]);

    } catch (Exception $e) {
        // Optional: error_log($e->getMessage());
    }
}
?>