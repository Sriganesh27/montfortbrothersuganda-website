<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// modules/students/partial/viewstudents.php

// Ensure Config is loaded if accessed directly via AJAX
if (!defined('CONFIG_LOADED')) {
    require_once __DIR__ . '/../../../config/config.php';
}

$filter_level = filter_input(INPUT_GET, 'level', FILTER_SANITIZE_STRING) ?? '';
$filter_class = filter_input(INPUT_GET, 'class', FILTER_SANITIZE_STRING) ?? '';
$filter_stream = filter_input(INPUT_GET, 'stream', FILTER_SANITIZE_STRING) ?? '';
$filter_term = filter_input(INPUT_GET, 'term', FILTER_SANITIZE_STRING) ?? '';
$filter_residence = filter_input(INPUT_GET, 'residence', FILTER_SANITIZE_STRING) ?? '';
$filter_gender = filter_input(INPUT_GET, 'gender', FILTER_SANITIZE_STRING) ?? '';
$filter_admission_year = filter_input(INPUT_GET, 'admission_year', FILTER_SANITIZE_NUMBER_INT) ?? '';
$search_id = filter_input(INPUT_GET, 'search_id', FILTER_SANITIZE_NUMBER_INT) ?? '';
$search_name = filter_input(INPUT_GET, 'search_name', FILTER_SANITIZE_STRING) ?? '';

$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$results = [];

if ($conn) {
    $sql = "
        SELECT 
            S.AdmissionNo, S.AdmissionYear, S.Name, S.Surname, S.PhotoPath, S.Gender,
            P.father_name, P.father_contact,
            P.mother_name, P.mother_contact,
            E.Class, E.Level, E.Residence, E.Stream, E.AcademicYear, E.Term
        FROM erp_students S
        JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo
        LEFT JOIN erp_parents P ON S.AdmissionNo = P.AdmissionNo        
        WHERE 1=1
    ";
    
    $params = [];
    $types = '';
    
    if (!empty($search_id)) {
        $sql .= " AND S.AdmissionNo = ?";
        $types .= 'i';
        $params[] = $search_id;
    } else {
        if (!empty($search_name)) {
            $sql .= " AND (S.Name LIKE ? OR S.Surname LIKE ?)";
            $types .= 'ss';
            $term = '%' . $search_name . '%';
            $params[] = $term; $params[] = $term;
        }
        if (!empty($filter_level)) { $sql .= " AND E.Level = ?"; $types .= 's'; $params[] = $filter_level; }
        if (!empty($filter_class)) { $sql .= " AND E.Class = ?"; $types .= 's'; $params[] = $filter_class; }
        if (!empty($filter_stream)) { $sql .= " AND E.Stream = ?"; $types .= 's'; $params[] = $filter_stream; }
        if (!empty($filter_term)) { $sql .= " AND E.Term = ?"; $types .= 's'; $params[] = $filter_term; }
        if (!empty($filter_admission_year)) { $sql .= " AND S.AdmissionYear = ?"; $types .= 'i'; $params[] = $filter_admission_year; }
        if (!empty($filter_residence)) { $sql .= " AND E.Residence = ?"; $types .= 's'; $params[] = $filter_residence; }
        if (!empty($filter_gender)) { $sql .= " AND S.Gender = ?"; $types .= 's'; $params[] = $filter_gender; }
    }

    $sql .= " ORDER BY S.AdmissionNo ASC";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($types)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) $results[] = $row;
        $stmt->close();
    }
    $conn->close();
}
?>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Student</th>
                <th>Class Info</th>
                <th>Father</th>
                <th>Mother</th>
                <th>Residence</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($results)): ?>
                <tr><td colspan="7" style="text-align: center; padding: 20px;">No records found.</td></tr>
            <?php else: ?>
                <?php foreach ($results as $student): ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($student['PhotoPath'] ?? 'static/images/default_profile.png'); ?>" class="student-photo-thumb"></td>
                        <td>
                            <strong><?php echo htmlspecialchars($student['Name'] . ' ' . $student['Surname']); ?></strong><br>
                            <small>Adm-no: <?php echo $student['AdmissionNo']; ?> | <?php echo $student['Gender']; ?></small>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($student['Class']); ?> 
                            <?php echo $student['Stream'] ? '<span class="badge">'.$student['Stream'].'</span>' : ''; ?><br>
                            <small><?php echo htmlspecialchars($student['AcademicYear']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($student['father_name'] ?? '-'); ?><br><small><?php echo htmlspecialchars($student['father_contact'] ?? ''); ?></small></td>
                        <td><?php echo htmlspecialchars($student['mother_name'] ?? '-'); ?><br><small><?php echo htmlspecialchars($student['mother_contact'] ?? ''); ?></small></td>
                        <td><?php echo htmlspecialchars($student['Residence']); ?><br><small><?php echo htmlspecialchars($student['Term']); ?></small></td>
                        <td>
                            <div style="display:flex; gap:5px; justify-content:center; align-items:center;">
                                <button class="btn-action view-btn" onclick="loadProfileViaAjax(<?php echo $student['AdmissionNo']; ?>)">
                                    <i class="fa fa-user" style="padding: 2px;"></i> Profile
                                </button>
                                <button class="btn-action delete-btn js-delete-student" data-id="<?php echo $student['AdmissionNo']; ?>" data-name="<?php echo htmlspecialchars($student['Name'] . ' ' . $student['Surname']); ?>">
                                    <i class="fa fa-trash"></i>Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
