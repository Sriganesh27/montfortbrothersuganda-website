<?php include __DIR__ . '/../../layouts/header.php'; ?>

<?php
// webpages/studentsummary.php

if (!defined('CONFIG_LOADED')) { if (!isset($DB_HOST)) { require_once __DIR__ . '/../../../config/config.php'; } }
global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
$conn = get_db_connection($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

$ALL_CLASSES_MAP = [
    'pre-primary' => ['N1', 'N2', 'N3'],
    'primary' => ['P1', 'P2', 'P3', 'P4', 'P5', 'P6', 'P7'],
    'secondary' => ['S1', 'S2', 'S3', 'S4', 'S5', 'S6']
];
$ALL_CLASSES = [];
foreach ($ALL_CLASSES_MAP as $level => $classes) { $ALL_CLASSES = array_merge($ALL_CLASSES, $classes); }

$summary_data = [
    'total_students' => 0,
    'by_level' => [],
    'by_gender' => [],
    'by_residence' => [],
    'by_class_stream_entry' => [], // UPDATED
    'by_class_stream_gender' => [] // UPDATED
];

// Default to 'All' for unified search if not specified
$filter_term = filter_input(INPUT_GET, 'summary_term', FILTER_SANITIZE_STRING) ?? 'All';
$filter_year = filter_input(INPUT_GET, 'summary_year', FILTER_SANITIZE_STRING) ?? format_academic_year(date('Y'));

$where_clauses = [];
$params = [];
$types = '';

if ($filter_term !== 'All') {
    $where_clauses[] = "E.Term = ?";
    $params[] = $filter_term;
    $types .= 's';
}
if ($filter_year !== 'All') {
    $where_clauses[] = "E.AcademicYear = ?";
    $params[] = $filter_year;
    $types .= 's';
}

$where_sql = count($where_clauses) > 0 ? " WHERE " . implode(' AND ', $where_clauses) : "";

if ($conn) {
    function fetch_summary_data($conn, $sql, $params = [], $types = '') {
        $data = [];
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($types)) $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) $data[] = $row;
            $stmt->close();
        }
        return $data;
    }
    
    // 1. Total & Basic Stats (Unchanged logic)
    $sql_total = "SELECT COUNT(S.AdmissionNo) AS count FROM erp_students S JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo" . $where_sql;
    $res = fetch_summary_data($conn, $sql_total, $params, $types);
    $summary_data['total_students'] = $res[0]['count'] ?? 0;

    $summary_data['by_level'] = fetch_summary_data($conn, "SELECT E.Level, COUNT(S.AdmissionNo) AS count FROM erp_students S JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo $where_sql GROUP BY E.Level ORDER BY count DESC", $params, $types);
    $summary_data['by_gender'] = fetch_summary_data($conn, "SELECT S.Gender, COUNT(S.AdmissionNo) AS count FROM erp_students S JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo $where_sql GROUP BY S.Gender ORDER BY count DESC", $params, $types);
    $summary_data['by_residence'] = fetch_summary_data($conn, "SELECT E.Residence, COUNT(S.AdmissionNo) AS count FROM erp_students S JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo $where_sql GROUP BY E.Residence ORDER BY count DESC", $params, $types);

    // 2. UPDATED: Class AND Stream Grouping
    // Entry Status by Class + Stream
    $sql_entry = "SELECT E.Class, E.Stream, E.EntryStatus, COUNT(S.AdmissionNo) AS count FROM erp_students S JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo $where_sql GROUP BY E.Class, E.Stream, E.EntryStatus ORDER BY E.Class, E.Stream";
    $summary_data['by_class_stream_entry'] = fetch_summary_data($conn, $sql_entry, $params, $types);

    // Gender by Class + Stream
    $sql_class_gender = "SELECT E.Class, E.Stream, S.Gender, COUNT(S.AdmissionNo) AS count FROM erp_students S JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo $where_sql GROUP BY E.Class, E.Stream, S.Gender ORDER BY E.Class, E.Stream";
    $summary_data['by_class_stream_gender'] = fetch_summary_data($conn, $sql_class_gender, $params, $types);

    $conn->close();
}

function calculate_percentage($count, $total) { return ($total == 0) ? 0 : round(($count / $total) * 100, 1); }
$total = $summary_data['total_students'];
?>

<div class="summary-container">
    <p>Overview for <?php echo htmlspecialchars($filter_year); ?>, Term: <?php echo htmlspecialchars($filter_term); ?></p>

    <div class="overview-cards-container">
        <div class="summary-card total-card">
            <i class="fa fa-users"></i>
            <div><span class="card-label">Total Enrollment</span><p><?php echo $total; ?></p></div>
        </div>
        <div class="summary-card stat-card gender-card">
            <i class="fa fa-venus-mars"></i>
            <div style="flex-grow:1;">
                <span class="card-label">Gender</span>
                <?php 
                    $m=0; $f=0;
                    foreach($summary_data['by_gender'] as $g) { if($g['Gender']=='Male')$m=$g['count']; if($g['Gender']=='Female')$f=$g['count']; }
                    $mp = calculate_percentage($m, $total); $fp = calculate_percentage($f, $total);
                ?>
                <div class="stat-bar-group">
                    <div class="stat-bar-item male-bar" style="width:<?php echo $mp; ?>%"></div>
                    <div class="stat-bar-item female-bar" style="width:<?php echo $fp; ?>%"></div>
                </div>
                <div class="stat-values"><span>Male: <?php echo $m; ?> (<?php echo $mp; ?>%)</span><span>Female: <?php echo $f; ?> (<?php echo $fp; ?>%)</span></div>
            </div>
        </div>
    </div>

    <div class="tabbed-details-container">
        <div class="summary-tabs">
            <button class="tab-button active" data-tab="entry-summary">Class & Stream List</button>
            <button class="tab-button" data-tab="gender-summary"> Gender Detail</button>
            <button class="tab-button" data-tab="residence-summary">Residence Detail</button>
            </div>

        <div id="entry-summary" class="tab-content active">
            <h3>Enrollment by Class & Stream</h3>
            <div class="table-container">
                <table class="data-table summary-table">
                    <thead><tr><th>Class</th><th>Stream</th><th>Continuing</th><th>New</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php 
                        // Organize Data for Display
                        $grouped = [];
                        foreach ($summary_data['by_class_stream_entry'] as $row) {
                            $k = $row['Class'] . '_' . ($row['Stream'] ?? '');
                            if (!isset($grouped[$k])) $grouped[$k] = ['C' => $row['Class'], 'S' => $row['Stream'], 'New' => 0, 'Continuing' => 0];
                            $grouped[$k][$row['EntryStatus']] = $row['count'];
                        }
                        
                        $gt=0; $gn=0; $gc=0;
                        foreach ($grouped as $row) {
                            $t = $row['New'] + $row['Continuing'];
                            $gt += $t; $gn += $row['New']; $gc += $row['Continuing'];
                            echo "<tr>
                                <td>{$row['C']}</td>
                                <td>" . ($row['S'] ? $row['S'] : '-') . "</td>
                                <td>{$row['Continuing']}</td>
                                <td>{$row['New']}</td>
                                <td><strong>$t</strong></td>
                            </tr>";
                        }
                        ?>
                        <tr class="summary-total-row"><td>TOTAL</td><td>-</td><td><?php echo $gc; ?></td><td><?php echo $gn; ?></td><td><?php echo $gt; ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="gender-summary" class="tab-content">
            <h3>Gender by Class & Stream</h3>
            <div class="table-container">
                <table class="data-table summary-table">
                    <thead><tr><th>Class</th><th>Stream</th><th>Male</th><th>Female</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php 
                        $groupedG = [];
                        foreach ($summary_data['by_class_stream_gender'] as $row) {
                            $k = $row['Class'] . '_' . ($row['Stream'] ?? '');
                            if (!isset($groupedG[$k])) $groupedG[$k] = ['C' => $row['Class'], 'S' => $row['Stream'], 'Male' => 0, 'Female' => 0];
                            $groupedG[$k][$row['Gender']] = $row['count'];
                        }
                        
                        $tm=0; $tf=0;
                        foreach ($groupedG as $row) {
                            $t = $row['Male'] + $row['Female'];
                            $tm += $row['Male']; $tf += $row['Female'];
                            echo "<tr>
                                <td>{$row['C']}</td>
                                <td>" . ($row['S'] ? $row['S'] : '-') . "</td>
                                <td>{$row['Male']}</td>
                                <td>{$row['Female']}</td>
                                <td><strong>$t</strong></td>
                            </tr>";
                        }
                        ?>
                        <tr class="summary-total-row"><td>TOTAL</td><td>-</td><td><?php echo $tm; ?></td><td><?php echo $tf; ?></td><td><?php echo $tm+$tf; ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="residence-summary" class="tab-content">
            <h3>Residence Breakdown</h3>
            <div class="table-container">
                <table class="data-table summary-table">
                    <thead><tr><th>Type</th><th>Count</th><th>%</th></tr></thead>
                    <tbody>
                        <?php $tr=0; foreach($summary_data['by_residence'] as $r){ $tr+=$r['count']; echo "<tr><td>{$r['Residence']}</td><td>{$r['count']}</td><td>".calculate_percentage($r['count'], $total)."%</td></tr>"; } ?>
                        <tr class="summary-total-row"><td>TOTAL</td><td><?php echo $tr; ?></td><td>100%</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
