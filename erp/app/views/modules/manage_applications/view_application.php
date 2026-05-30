<?php 
date_default_timezone_set('Africa/Kampala'); 

// 1. SMART BASE URL FUNCTION (Always accessible, fixes the variable scope error)
function getBaseUrl() {
    $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    return rtrim($base, '/');
}

// 2. SMART PATH CLEANER
function getCleanUrl($db_path) {
    if (empty($db_path)) return '';
    $path = str_replace('\\', '/', $db_path);
    $assets_pos = strpos($path, 'assets/');
    
    if ($assets_pos !== false) {
        $clean_path = substr($path, $assets_pos);
        $final_url = getBaseUrl() . '/' . $clean_path;
        return preg_replace('#(?<!:)//+#', '/', $final_url);
    }
    return $path;
}

function displayField($val) {
    return (!empty(trim((string)$val))) ? htmlspecialchars($val) : '-';
}

function displaySubjects($jsonStr) {
    if(empty(trim((string)$jsonStr))) return '<span style="font-size:13px;">No specific subjects declared.</span>';
    $arr = json_decode($jsonStr, true);
    if(json_last_error() === JSON_ERROR_NONE && is_array($arr)) {
        if(count($arr) == 0) return '<span style="font-size:13px;">No subjects declared.</span>';
        $out = '<table style="width:100%; border-collapse:collapse; margin-top:5px; font-size:13px;">';
        $out .= '<tr style="background:#e2e8f0;"><th style="border:1px solid #000; padding:6px; text-align:left;">Subject</th><th style="border:1px solid #000; padding:6px; width:100px; text-align:center;">Marks</th><th style="border:1px solid #000; padding:6px; width:100px; text-align:center;">Grade</th></tr>';
        foreach($arr as $sub) {
            $name = htmlspecialchars($sub['name'] ?? array_values($sub)[0] ?? '');
            $mark = htmlspecialchars($sub['mark'] ?? array_values($sub)[1] ?? '-');
            $grade = htmlspecialchars($sub['grade'] ?? array_values($sub)[2] ?? '-');
            $out .= "<tr><td style='border:1px solid #000; padding:6px;'>$name</td><td style='border:1px solid #000; padding:6px; text-align:center;'>$mark</td><td style='border:1px solid #000; padding:6px; text-align:center;'>$grade</td></tr>";
        }
        $out .= '</table>';
        return $out;
    }
    return htmlspecialchars($jsonStr);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application - <?php echo displayField($data['ref_number'] ?? ''); ?></title>
    
    <link rel="icon" type="image/webp" href="<?php echo getBaseUrl(); ?>/assets/Images/logo_MBSG_UG_8.webp">
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>/assets/styles/print_report.css">
</head>
<body>

    <div class="action-bar no-print">
        <?php if(!empty($data['prev_marks_doc'])): ?>
            <a href="<?php echo getCleanUrl($data['prev_marks_doc']); ?>" target="_blank" class="btn-view-doc">View Attached Document</a>
        <?php endif; ?>
        
        <button class="btn-print" onclick="window.print()">Print Form</button>
    </div>

    <div class="document-container">
        
        <div class="header-section">
            <img src="<?php echo getBaseUrl(); ?>/assets/Images/logo_MBSG_UG_8.webp" alt="Logo" class="logo-placeholder" onerror="this.style.display='none'">
            <div class="header-text">
                <h1>Montfort Brothers of St. Gabriel</h1>
                <h2>Application for Admission</h2>
                <p><?php echo displayField($data['branch_name'] ?? 'General Branch'); ?></p>
            </div>
            <div style="width: 100px;"></div>
        </div>

        <div class="meta-info">
            <div>Ref No: <span style="font-weight:bold; font-size:15px; color:#1a365d;"><?php echo displayField($data['ref_number'] ?? ''); ?></span></div>
            <div>Date: <span><?php echo displayField($data['date_of_registration'] ?? ''); ?></span></div>
            <div>Status: <span style="font-weight:bold; color:#2f855a;"><?php echo displayField($data['status'] ?? 'Pending'); ?></span></div>
            <div>Scholarship: <span style="color:#d32f2f; font-weight:bold;"><?php echo displayField($data['scholarship_status'] ?? 'None'); ?></span></div>
        </div>

        <div class="form-section">
            <div class="form-section-title">1. Student Details</div>
            <div style="display: grid; grid-template-columns: 3fr 1fr;">
                <div>
                    <div class="grid-row full-width">
                        <div class="grid-item full-width"><span class="label">Full Name</span><span class="value"><?php echo displayField(trim(($data['student_name'] ?? '') . ' ' . ($data['middle_name'] ?? '') . ' ' . ($data['student_surname'] ?? ''))); ?></span></div>
                    </div>
                    <div class="grid-row three-col">
                        <div class="grid-item"><span class="label">Gender</span><span class="value"><?php echo displayField($data['gender'] ?? ''); ?></span></div>
                        <div class="grid-item"><span class="label">Date of Birth</span><span class="value"><?php echo displayField($data['dob'] ?? ''); ?></span></div>
                        <div class="grid-item"><span class="label">Nationality</span><span class="value"><?php echo displayField($data['nationality'] ?? ''); ?></span></div>
                    </div>
                    <div class="grid-row">
                        <div class="grid-item no-bottom"><span class="label">Academic Year / Term</span><span class="value"><?php echo displayField($data['academic_year'] ?? '') . ' / ' . displayField($data['term'] ?? ''); ?></span></div>
                        <div class="grid-item no-bottom"><span class="label">Applied Class</span><span class="value"><?php echo displayField($data['applied_class'] ?? '') . ' (' . displayField($data['level'] ?? '') . ')'; ?></span></div>
                    </div>
                </div>
                <div class="photo-area">
                    <?php 
                    $photo = getCleanUrl($data['photo_path'] ?? '');
                    if(!empty($photo)): ?>
                        <img src="<?php echo $photo; ?>" class="student-photo" alt="Photo">
                    <?php else: ?>
                        <div class="no-photo">Affix Photo</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">2. Parents & Guardian Details</div>
            <div class="grid-row three-col" style="background:#f8fafc; padding:4px 12px; border-bottom:1px solid #000;"><span class="label">FATHER'S PROFILE</span></div>
            <div class="grid-row three-col">
                <div class="grid-item"><span class="label">Name</span><span class="value"><?php echo displayField($data['father_name'] ?? ''); ?></span></div>
                <div class="grid-item"><span class="label">Contact Number</span><span class="value"><?php echo displayField($data['father_contact'] ?? ''); ?></span></div>
                <div class="grid-item"><span class="label">Email Address</span><span class="value"><?php echo displayField($data['father_email'] ?? ''); ?></span></div>
                <div class="grid-item no-bottom"><span class="label">Occupation</span><span class="value"><?php echo displayField($data['father_occupation'] ?? ''); ?></span></div>
                <div class="grid-item no-bottom"><span class="label">Education Level</span><span class="value"><?php echo displayField($data['father_education'] ?? ''); ?></span></div>
                <div class="grid-item no-bottom"><span class="label">Age</span><span class="value"><?php echo displayField($data['father_age'] ?? ''); ?></span></div>
            </div>

            <div class="grid-row three-col" style="background:#f8fafc; padding:4px 12px; border-top:1px solid #000; border-bottom:1px solid #000;"><span class="label">MOTHER'S PROFILE</span></div>
            <div class="grid-row three-col">
                <div class="grid-item"><span class="label">Name</span><span class="value"><?php echo displayField($data['mother_name'] ?? ''); ?></span></div>
                <div class="grid-item"><span class="label">Contact Number</span><span class="value"><?php echo displayField($data['mother_contact'] ?? ''); ?></span></div>
                <div class="grid-item"><span class="label">Email Address</span><span class="value"><?php echo displayField($data['mother_email'] ?? ''); ?></span></div>
                <div class="grid-item no-bottom"><span class="label">Occupation</span><span class="value"><?php echo displayField($data['mother_occupation'] ?? ''); ?></span></div>
                <div class="grid-item no-bottom"><span class="label">Education Level</span><span class="value"><?php echo displayField($data['mother_education'] ?? ''); ?></span></div>
                <div class="grid-item no-bottom"><span class="label">Age</span><span class="value"><?php echo displayField($data['mother_age'] ?? ''); ?></span></div>
            </div>

            <div class="grid-row three-col" style="background:#f8fafc; padding:4px 12px; border-top:1px solid #000; border-bottom:1px solid #000;"><span class="label">GUARDIAN'S PROFILE</span></div>
            <div class="grid-row three-col">
                <div class="grid-item"><span class="label">Name</span><span class="value"><?php echo displayField($data['guardian_name'] ?? ''); ?></span></div>
                <div class="grid-item"><span class="label">Relationship</span><span class="value"><?php echo displayField($data['guardian_relation'] ?? ''); ?></span></div>
                <div class="grid-item"><span class="label">Contact Number</span><span class="value"><?php echo displayField($data['guardian_contact'] ?? ''); ?></span></div>
                <div class="grid-item no-bottom"><span class="label">Email Address</span><span class="value"><?php echo displayField($data['guardian_email'] ?? ''); ?></span></div>
                <div class="grid-item no-bottom"><span class="label">Occupation</span><span class="value"><?php echo displayField($data['guardian_occupation'] ?? ''); ?></span></div>
                <div class="grid-item no-bottom"><span class="label">Education & Age</span><span class="value"><?php echo displayField($data['guardian_education'] ?? ''); ?> | Age: <?php echo displayField($data['guardian_age'] ?? ''); ?></span></div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">3. Residential Address</div>
            <div class="grid-row three-col">
                <div class="grid-item no-bottom"><span class="label">House / Street</span><span class="value"><?php echo displayField($data['address_house'] ?? '') . ', ' . displayField($data['address_street'] ?? ''); ?></span></div>
                <div class="grid-item no-bottom"><span class="label">Village / District / State</span><span class="value"><?php echo displayField($data['address_village'] ?? '') . ' / ' . displayField($data['address_district'] ?? '') . ' / ' . displayField($data['address_state'] ?? ''); ?></span></div>
                <div class="grid-item no-bottom"><span class="label">Postal Code / Country</span><span class="value"><?php echo displayField($data['address_postal'] ?? '') . ' / ' . displayField($data['address_country'] ?? ''); ?></span></div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title">4. Academic History & Additional Info</div>
            <div class="grid-row three-col">
                <div class="grid-item"><span class="label">Former School</span><span class="value"><?php echo displayField($data['former_school'] ?? ''); ?></span></div>
                <div class="grid-item"><span class="label">School Code</span><span class="value"><?php echo displayField($data['former_school_code'] ?? ''); ?></span></div>
                <div class="grid-item"><span class="label">LIN (Learner ID)</span><span class="value"><?php echo displayField($data['former_school_lin'] ?? ''); ?></span></div>
            </div>
            
            <div class="grid-row">
                <div class="grid-item"><span class="label">PLE Index & Score</span><span class="value"><?php echo displayField($data['ple_ref'] ?? '') . ' / ' . displayField($data['ple_score'] ?? ''); ?></span></div>
                <div class="grid-item"><span class="label">UCE Index & Score</span><span class="value"><?php echo displayField($data['uce_ref'] ?? '') . ' / ' . displayField($data['uce_score'] ?? ''); ?></span></div>
            </div>

            <div class="grid-item full-width">
                <span class="label">Subject-Wise Marks Submitted</span>
                <?php echo displaySubjects($data['subject_marks'] ?? ''); ?>
            </div>
            <div class="grid-item full-width no-bottom">
                <span class="label">Medical Conditions / Extra Information</span>
                <span class="value"><?php echo displayField($data['more_info'] ?? 'None declared.'); ?></span>
            </div>
        </div>

        <div class="signature-box">
            <div class="sig-column">
                <div class="sig-line"></div>
                <span>Signature of Parent/Guardian</span>
            </div>
            <div class="date-column">
                <div class="sig-line"></div>
                <span>Date</span>
            </div>
        </div>

        <div class="footer">
            Printed from MBSG ERP System on <?php echo date('F j, Y - H:i'); ?>
        </div>
    </div>

</body>
</html>