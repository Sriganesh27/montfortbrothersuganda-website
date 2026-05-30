<?php

class ApplyModel
{
    private $conn;

    public function __construct()
    {
        // Establish the database connection so the print query works
        $this->conn = get_db_connection(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    // This fetches the application data for the student receipt
    public function getApplicationByRef($ref_number)
    {
        $sql = "SELECT a.*, b.branch_name 
                FROM erp_applications a
                LEFT JOIN erp_branches b ON a.branch_id = b.branch_id
                WHERE a.ref_number = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ref_number);
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        $stmt->close();
        return $data;
    }
}
?>