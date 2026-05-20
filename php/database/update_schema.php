<?php
/**
 * update_schema.php — Brings an existing database up to date.
 *
 * Run this ONCE in your browser after pulling new code:
 *   http://localhost/CEDULA/php/database/update_schema.php
 *
 * What it does (safe to run multiple times):
 *   1. Adds the 'status' column to the users table (pending / approved / rejected).
 *   2. Adds / updates the 'request_type' column on user_block_requests
 *      so it includes 'registration' (needed by signup.php).
 *   3. Re-creates every foreign key that points to users(id) so they
 *      include ON UPDATE CASCADE — this fixes the
 *      "#1451 Cannot delete or update a parent row" error when
 *      editing a user's ID in phpMyAdmin.
 */
require_once 'db_connect.php';

// ─── Helper ──────────────────────────────────────────────────
// Drops an old FK and adds a new one with ON UPDATE CASCADE.
// If the old FK doesn't exist the DROP silently fails, then
// the ADD creates it fresh.
function rebuildForeignKey($conn, $table, $oldFkName, $column, $refTable, $refColumn, $onDelete = 'CASCADE') {
    // Step 1: try to drop the old constraint
    $conn->query("ALTER TABLE `$table` DROP FOREIGN KEY `$oldFkName`");

    // Step 2: add the constraint back with ON UPDATE CASCADE
    $onDeleteClause = ($onDelete === 'SET NULL') ? 'ON DELETE SET NULL' : 'ON DELETE CASCADE';
    $sql = "ALTER TABLE `$table`
            ADD CONSTRAINT `$oldFkName`
            FOREIGN KEY (`$column`) REFERENCES `$refTable` (`$refColumn`)
            $onDeleteClause ON UPDATE CASCADE";
    $conn->query($sql);
}

// ─── 1. users.status column ─────────────────────────────────
// New registrations start as 'pending' until an admin approves them.
// The column may already exist, so we check first to avoid a
// "Duplicate column name" error.
$result = $conn->query("SHOW COLUMNS FROM `users` LIKE 'status'");
if ($result && $result->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN status ENUM('pending','registered','rejected') NOT NULL DEFAULT 'pending' AFTER role");
    // All users that existed BEFORE the column was added should be
    // treated as already registered (they were able to log in before).
    $conn->query("UPDATE users SET status = 'registered'");
} else {
    // Modify existing enum to ensure 'registered' is included
    $conn->query("ALTER TABLE users MODIFY COLUMN status ENUM('pending','registered','rejected') NOT NULL DEFAULT 'pending'");
    // Rename 'approved' to 'registered' if it exists (for backward compatibility if run before)
    $conn->query("UPDATE users SET status = 'registered' WHERE status = 'approved'");
}
// Safety net: if the column was added in a previous run but
// existing users were left as 'pending', fix them now.
// Only touches users who were never explicitly set to 'pending'
// by the signup flow (those have a matching user_block_requests row).
$conn->query("UPDATE users SET status = 'registered' WHERE status = 'pending' AND id NOT IN (SELECT target_id FROM user_block_requests WHERE request_type = 'registration' AND status = 'pending')");

// ─── 2. user_block_requests.request_type column ──────────────
// 'registration' is used by signup.php for new-user approval requests.
$result = $conn->query("SHOW COLUMNS FROM `user_block_requests` LIKE 'request_type'");
if ($result && $result->num_rows === 0) {
    $conn->query("ALTER TABLE user_block_requests ADD COLUMN request_type ENUM('block','unblock','registration') DEFAULT 'block' AFTER target_id");
} else {
    // Column exists but might not include 'registration' — update the enum
    $conn->query("ALTER TABLE user_block_requests MODIFY COLUMN request_type ENUM('block','unblock','registration') DEFAULT 'block'");
}

// ─── 3. Rebuild all foreign keys → users(id) with ON UPDATE CASCADE ──
// This fixes the "#1451 Cannot update a parent row" error that
// happens when someone edits a user's ID in phpMyAdmin.

// password_reset_otp → users
rebuildForeignKey($conn, 'password_reset_otp', 'password_reset_otp_ibfk_1', 'user_id', 'users', 'id');

// orders → users
rebuildForeignKey($conn, 'orders', 'fk_orders_user', 'user_id', 'users', 'id');

// payment_methods → users
rebuildForeignKey($conn, 'payment_methods', 'fk_payment_user', 'user_id', 'users', 'id');

// user_favorites → users
rebuildForeignKey($conn, 'user_favorites', 'fk_fav_user', 'user_id', 'users', 'id');

// login_logs → users
rebuildForeignKey($conn, 'login_logs', 'fk_logs_user', 'user_id', 'users', 'id');

// user_block_requests → users (requester)
rebuildForeignKey($conn, 'user_block_requests', 'fk_block_requester', 'requester_id', 'users', 'id');

// user_block_requests → users (target)
rebuildForeignKey($conn, 'user_block_requests', 'fk_block_target', 'target_id', 'users', 'id');

// cart → users
rebuildForeignKey($conn, 'cart', 'fk_cart_user', 'user_id', 'users', 'id');

// approvals → users (requested_by)
rebuildForeignKey($conn, 'approvals', 'approvals_requested_by_fk', 'requested_by', 'users', 'id');

// approvals → users (reviewed_by) — uses SET NULL on delete
rebuildForeignKey($conn, 'approvals', 'approvals_reviewed_by_fk', 'reviewed_by', 'users', 'id', 'SET NULL');

// admin_creation_requests → users (requested_by)
rebuildForeignKey($conn, 'admin_creation_requests', 'admin_creation_requested_by_fk', 'requested_by', 'users', 'id');

// admin_creation_requests → users (reviewed_by) — uses SET NULL on delete
rebuildForeignKey($conn, 'admin_creation_requests', 'admin_creation_reviewed_by_fk', 'reviewed_by', 'users', 'id', 'SET NULL');

// ─── 4. Repair corrupted roles ──────────────────────────────
// A previous bind_param bug could set role to '' (empty string).
// Restore seed users to their known roles, and default any
// remaining empty-role users to 'consumer'.
$conn->query("UPDATE users SET role = 'superadmin' WHERE username = 'juancruz123' AND (role = '' OR role IS NULL)");
$conn->query("UPDATE users SET role = 'admin' WHERE username = 'jisa123' AND (role = '' OR role IS NULL)");
$conn->query("UPDATE users SET role = 'consumer' WHERE role = '' OR role IS NULL");

// ─── 5. Add UNIQUE constraint on email ──────────────────────
// Prevents duplicate emails at the database level.
// First check if it already exists.
$hasEmailUnique = false;
$idxResult = $conn->query("SHOW INDEX FROM users WHERE Column_name = 'email' AND Non_unique = 0");
if ($idxResult && $idxResult->num_rows > 0) {
    $hasEmailUnique = true;
}

if (!$hasEmailUnique) {
    // Check for existing duplicates before adding the constraint
    $dupCheck = $conn->query("SELECT email, COUNT(*) as cnt FROM users GROUP BY email HAVING cnt > 1");
    if ($dupCheck && $dupCheck->num_rows > 0) {
        echo "WARNING: Cannot add UNIQUE constraint on email — duplicate emails exist:\n";
        while ($row = $dupCheck->fetch_assoc()) {
            echo "  Email '{$row['email']}' appears {$row['cnt']} times.\n";
        }
        echo "Fix these duplicates first, then run this script again.\n\n";
    } else {
        $conn->query("ALTER TABLE users ADD UNIQUE KEY `email` (`email`)");
        echo "UNIQUE constraint added on users.email. ";
    }
}

echo "Schema updated successfully.";
$conn->close();
?>
