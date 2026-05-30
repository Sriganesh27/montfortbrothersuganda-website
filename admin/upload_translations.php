<?php
// weblangu/admin/upload_translations.php
session_start();

if (empty($_SESSION['admin_logged_in'])) {
    die("Unauthorized access. Admin privileges required.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = "Upload failed with error code: " . $file['error'];
    } elseif ($file['size'] > 2097152) { 
        $message = "File is too large. Max size is 2MB.";
    } elseif (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
        $message = "Invalid file type. Please upload a .csv file.";
    } else {
        $handle = fopen($file['tmp_name'], "r");
        if ($handle !== FALSE) {
            $headers = fgetcsv($handle, 1000, ",");
            $headers = array_map('strtolower', array_map('trim', $headers));
            
            if ($headers[0] !== 'key') {
                $message = "Invalid CSV format. The first column header must be 'key'.";
            } else {
                $json_data = [];
                for ($i = 1; $i < count($headers); $i++) {
                    $json_data[$headers[$i]] = [];
                }

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $key = trim($data[0] ?? '');
                    if (empty($key)) continue; 
                    
                    for ($i = 1; $i < count($headers); $i++) {
                        $lang = $headers[$i];
                        $json_data[$lang][$key] = trim($data[$i] ?? '');
                    }
                }
                fclose($handle);

                $storage_dir = __DIR__ . '/../storage';
                if (!is_dir($storage_dir . '/backups')) mkdir($storage_dir . '/backups', 0755, true);

                $json_file = $storage_dir . '/translations.json';
                if (file_exists($json_file)) {
                    copy($json_file, $storage_dir . '/backups/translations_' . date('Ymd_His') . '.json');
                }

                if (file_put_contents($json_file, json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                    $message = "Translations successfully updated!";
                } else {
                    $message = "Error writing to storage directory.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Translations</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .box { max-width: 500px; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .msg { padding: 10px; margin-bottom: 15px; background: #eef; border-left: 4px solid blue; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Upload Dictionary (CSV)</h2>
        <?php if ($message) echo "<div class='msg'>".htmlspecialchars($message)."</div>"; ?>
        <p>Upload a CSV file containing your translations. The first row must be headers: <strong>key, en, fr, es</strong>.</p>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" required><br><br>
            <button type="submit">Upload and Convert</button>
        </form>
    </div>
</body>
</html>