<?php

class StudentsummaryModel
{
    // Original File: modules/students/partial/studentsummary.php


    // Extracted SQL references from original code
    // $sql_total = "SELECT COUNT(S.AdmissionNo) AS count FROM erp_students S JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo" . $where_sql;
    // $summary_data['by_level'] = fetch_summary_data($conn, "SELECT E.Level, COUNT(S.AdmissionNo) AS count FROM erp_students S JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo $where_sql GROUP BY E.Level ORDER BY count DESC", $params, $types);
    // $summary_data['by_gender'] = fetch_summary_data($conn, "SELECT S.Gender, COUNT(S.AdmissionNo) AS count FROM erp_students S JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo $where_sql GROUP BY S.Gender ORDER BY count DESC", $params, $types);
    // $summary_data['by_residence'] = fetch_summary_data($conn, "SELECT E.Residence, COUNT(S.AdmissionNo) AS count FROM erp_students S JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo $where_sql GROUP BY E.Residence ORDER BY count DESC", $params, $types);
    // $sql_entry = "SELECT E.Class, E.Stream, E.EntryStatus, COUNT(S.AdmissionNo) AS count FROM erp_students S JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo $where_sql GROUP BY E.Class, E.Stream, E.EntryStatus ORDER BY E.Class, E.Stream";
    // $sql_class_gender = "SELECT E.Class, E.Stream, S.Gender, COUNT(S.AdmissionNo) AS count FROM erp_students S JOIN erp_enrollment E ON S.AdmissionNo = E.AdmissionNo $where_sql GROUP BY E.Class, E.Stream, S.Gender ORDER BY E.Class, E.Stream";

}
?>