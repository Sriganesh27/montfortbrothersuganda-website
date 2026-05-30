<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// erp/create_super_admin.php
require_once __DIR__ . '/config/config.php';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the Super User credentials matching login_process routing rules
$superUsername = 'super_admin';
$superPassword = 'SuperAdmin@2026'; 
$role = 'Super User'; // FIXED: Changed 'Super Admin' to 'Super User'

echo "<h2>ERP Super User Creation</h2>";

$hashedPassword = password_hash($superPassword, PASSWORD_DEFAULT);

// Check if user exists
$checkSql = "SELECT id FROM erp_users WHERE username = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("s", $superUsername);
$checkStmt->execute();

if ($checkStmt->get_result()->num_rows > 0) {
    echo "<p style='color:orange;'>Skipped: Super User '<b>{$superUsername}</b>' already exists.</p>";
} else {
    // Insert with NO specific branch assignment (NULL) so they have global access
    $sql = "INSERT INTO erp_users (username, password, role, assigned_branch, is_active) 
            VALUES (?, ?, ?, NULL, 1)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $superUsername, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Success: Created Super User account (<b>{$superUsername}</b>)</p>";
    } else {
        echo "<p style='color:red;'>Error creating Super User: " . $conn->error . "</p>";
    }
}

echo "<br><span style='color:red;'><b>SECURITY WARNING:</b> Delete this file (create_super_admin.php) from your server immediately after running it!</span>";

$conn->close();
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
