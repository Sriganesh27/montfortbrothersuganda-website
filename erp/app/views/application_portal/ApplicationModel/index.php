<?php
// erp/app/views/application_portal/ApplicationModel/index.php

class ApplicationModel {
    private $conn;

    public function __construct($db) { $this->conn = $db; }

    public function getActiveBranches() {
        $result = $this->conn->query("SELECT branch_id, branch_name, school_code, branch_type FROM erp_branches");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function generateReferenceNumber($branch_id, $year) {
        $stmt_code = $this->conn->prepare("SELECT school_code FROM erp_branches WHERE branch_id = ?");
        $stmt_code->bind_param("i", $branch_id); $stmt_code->execute();
        $school_code = $stmt_code->get_result()->fetch_assoc()['school_code'] ?? 'U000'; 
        
        $prefix = $school_code . '-' . substr((string)$year, -2) . '-';
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM erp_applications WHERE branch_id = ? AND academic_year = ?");
        $stmt->bind_param("is", $branch_id, $year); $stmt->execute();
        $sequence = str_pad((string)(($stmt->get_result()->fetch_assoc()['total'] ?? 0) + 1), 3, '0', STR_PAD_LEFT);
        
        return $prefix . $sequence;
    }

    public function saveApplication($data) {
        $sql = "INSERT INTO erp_applications (
            ref_number, branch_id, academic_year, term, date_of_registration, 
            student_name, middle_name, student_surname, gender, dob, nationality,
            address_postal, address_house, address_street, address_village, address_district, address_state, address_country, 
            father_name, father_age, father_contact, father_email, father_occupation, father_education, 
            mother_name, mother_age, mother_contact, mother_email, mother_occupation, mother_education, 
            guardian_name, guardian_relation, guardian_age, guardian_contact, guardian_email, guardian_occupation, guardian_education, 
            level, applied_class, class_code, 
            former_school, former_school_code, former_school_lin, prev_marks_doc,
            ple_score, ple_ref, uce_score, uce_ref, subject_marks, more_info, photo_path
        ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) throw new Exception("Database SQL Error: " . $this->conn->error);
        
        // 51 Parameters total
        $stmt->bind_param(
            "sssssssssssssssssssssssssssssssssssssssssssssssssss", 
            $data['ref'], $data['branch'], $data['year'], $data['term'], $data['reg_date'], 
            $data['name'], $data['mname'], $data['surname'], $data['gender'], $data['dob'], $data['nationality'],
            $data['postal'], $data['house'], $data['street'], $data['village'], $data['district'], $data['state'], $data['country'], 
            $data['f_name'], $data['f_age'], $data['f_con'], $data['f_email'], $data['f_occ'], $data['f_edu'], 
            $data['m_name'], $data['m_age'], $data['m_con'], $data['m_email'], $data['m_occ'], $data['m_edu'], 
            $data['g_name'], $data['g_rel'], $data['g_age'], $data['g_con'], $data['g_email'], $data['g_occ'], $data['g_edu'], 
            $data['level'], $data['class_name'], $data['class_code'], 
            $data['former_school'], $data['former_school_code'], $data['former_school_lin'], $data['prev_marks_doc'],
            $data['ple_score'], $data['ple_ref'], $data['uce_score'], $data['uce_ref'], $data['subject_marks'], $data['more_info'], $data['photo_path']
        );
        
        return $stmt->execute();
    }
}
?>