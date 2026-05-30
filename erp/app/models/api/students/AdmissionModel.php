<?php

class AdmissionModel
{
    // Original File: api/students/admission.php


    // Extracted SQL references from original code
    // throw new Exception("Invalid Residence. Please select Day or Boarding.");
    // throw new Exception("Invalid Entry Status. Please select New or Continuing.");
    // $stmt_code = $conn->prepare("SELECT school_code, branch_name, branch_location FROM erp_branches WHERE branch_id = ?");
    // // --- INSERT STUDENT (Updated Fields) ---
    // $sql_stu = "INSERT INTO erp_students (AdmissionNo, branch_id, AdmissionYear, Name, MiddleName, Surname, DateOfBirth, Gender, Nationality, HouseNo, Street, Village, Town, District, State, Country, PostalCode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    // // --- INSERT PARENTS ---
    // $sql_par = "INSERT INTO erp_parents (AdmissionNo, branch_id, father_name, father_contact, father_email, father_age, father_occupation, father_education, mother_name, mother_contact, mother_email, mother_age, mother_occupation, mother_education, guardian_name, guardian_relation, guardian_contact, guardian_email, guardian_age, guardian_occupation, guardian_education, guardian_address, MoreInformation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    // // --- INSERT ACADEMIC HISTORY (Updated Fields) ---
    // $sql_hist = "INSERT INTO erp_academichistory (AdmissionNo, branch_id, FormerSchool, FormerSchoolCode, FormerSchoolLIN, PLEIndexNumber, PLEAggregate, UCEIndexNumber, UCEResult, SubjectMarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    // // --- INSERT ENROLLMENT ---
    // $sql_enr = "INSERT INTO erp_enrollment (AdmissionNo, branch_id, AcademicYear, Term, Class, Level, Stream, Residence, EntryStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    // $sql_acc = "INSERT INTO erp_student_accounts(AdmissionNo, branch_id, username, password, is_active) VALUES (?, ?, ?, ?, ?)";
    // $stmt_app = $conn->prepare("UPDATE erp_applications SET status = 'Admitted' WHERE app_id = ? AND branch_id = ?");
    // $stmt_p = $conn->prepare("UPDATE erp_students SET PhotoPath = ? WHERE AdmissionNo = ? AND branch_id = ?");

}
?>