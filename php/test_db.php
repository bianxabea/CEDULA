<?php
require 'database/db_connect.php';
echo "--- DB SCHEMA ---\n";
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_array()) {
    $table = $row[0];
    echo "\nTABLE: $table\n";
    $cols = $conn->query("DESCRIBE `$table`");
    if ($cols) {
        while ($c = $cols->fetch_assoc()) {
            echo "  - {$c['Field']} ({$c['Type']})\n";
        }
    }
    else {
        echo "  - Error describing table $table\n";
    }
}
echo "--- END ---\n";
