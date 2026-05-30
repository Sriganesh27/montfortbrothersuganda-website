<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// erp/create_all_admins.php
require_once __DIR__ . '/config/config.php';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the three school administrators
$admins = [
    [
        'username' => 'st_kizito_admin',
        'password' => 'Kizito@2026', // Change these to secure passwords
        'branch_id' => 1,
        'school' => 'St. Kizito Nursery and Primary'
    ],
    [
        'username' => 'st_montfort_admin',
        'password' => 'Montfort@2026',
        'branch_id' => 2,
        'school' => 'St. Montfort Nursery and Primary'
    ],
    [
        'username' => 'pere_achte_admin',
        'password' => 'PereAchte@2026',
        'branch_id' => 3,
        'school' => 'Pere Achte Senior Secondary'
    ]
];

$role = 'Branch Admin';

echo "<h2>ERP Branch Admin Creation</h2>";

foreach ($admins as $admin) {
    $hashedPassword = password_hash($admin['password'], PASSWORD_DEFAULT);
    
    // Check if user exists (Updated table name to erp_users)
    $checkSql = "SELECT id FROM erp_users WHERE username = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $admin['username']);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows > 0) {
        echo "<p style='color:orange;'>Skipped: User '<b>{$admin['username']}</b>' already exists.</p>";
        continue;
    }

    // Insert with branch assignment (Updated table name to erp_users)
    // Note: If your erp_users table does not have a 'created_at' column, remove it from this query.
    $sql = "INSERT INTO erp_users (username, password, role, assigned_branch, is_active) 
            VALUES (?, ?, ?, ?, 1)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $admin['username'], $hashedPassword, $role, $admin['branch_id']);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Success: Created admin for <b>{$admin['school']}</b> (ID: {$admin['branch_id']})</p>";
    } else {
        echo "<p style='color:red;'>Error creating {$admin['username']}: " . $conn->error . "</p>";
    }
}

echo "<br><span style='color:red;'><b>SECURITY WARNING:</b> Delete this file from your server immediately!</span>";

$conn->close();
?>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
