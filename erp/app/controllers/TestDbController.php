<?php
// erp/app/controllers/TestDbController.php

class TestDbController
{
    public function index()
    {
        // 1. Load config
        require_once __DIR__ . '/../../config/config.php';

        // 2. Test Connection
        // Assuming your config.php defines DB_HOST, DB_USER, etc.
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        echo "<h1>Database Connection Test</h1>";

        if ($conn->connect_error) {
            echo "<p style='color:red;'><strong>Failed:</strong> " . $conn->connect_error . "</p>";
            echo "<p>Check your credentials in <code>erp/config/config.php</code>.</p>";
        } else {
            echo "<p style='color:green;'><strong>Success!</strong> Connection established to database: " . DB_NAME . "</p>";
            $conn->close();
        }
    }
}
?>