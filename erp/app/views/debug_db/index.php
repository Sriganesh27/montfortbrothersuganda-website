<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// debug_db.php
require_once 'config/config.php';
require_once 'includes/db.php';

global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;

echo "Attempting to connect to: " . $DB_HOST . " using user: " . $DB_USER . "<br>";

$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn && !$conn->connect_error) {
    echo "SUCCESS: Database connected!<br>";
    
    // Check if the table actually exists
    $result = $conn->query("SHOW TABLES LIKE 'erp_branches'");
    if ($result->num_rows > 0) {
        echo "SUCCESS: Table 'erp_branches' exists.<br>";
        
        // Try to fetch one row
        $res = $conn->query("SELECT * FROM erp_branches LIMIT 1");
        if ($res) {
            echo "SUCCESS: Can read from 'erp_branches'. You should see schools below:<br>";
            print_r($res->fetch_assoc());
        } else {
            echo "ERROR: Table exists but cannot be read: " . $conn->error;
        }
    } else {
        echo "ERROR: Table 'erp_branches' does not exist in the database! (Did you run the SQL on the server?)";
    }
} else {
    echo "ERROR: Connection failed: " . mysqli_connect_error();
}
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
