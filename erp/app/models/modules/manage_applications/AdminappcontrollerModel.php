<?php
// erp/app/models/modules/manage_applications/AdminappcontrollerModel.php

class AdminappcontrollerModel
{
    private $conn;

    public function __construct()
    {
        // Establish the database connection using your config variables
        // (Assumes config.php has already been loaded by the Router)
        $this->conn = get_db_connection(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if (!$this->conn) {
            die(json_encode(['success' => false, 'message' => 'Database connection failed in Model.']));
        }
    }

    /**
     * Fetch a list of applications for the table
     */
    public function getApplicationsList($branch_id, $statusFilter = 'All')
    {
        if ($statusFilter === 'All') {
            $sql = "SELECT app_id, ref_number, student_name, student_surname, applied_class, status, scholarship_status, created_at 
                    FROM erp_applications 
                    WHERE branch_id = ? ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $branch_id);
        } else {
            $sql = "SELECT app_id, ref_number, student_name, student_surname, applied_class, status, scholarship_status, created_at 
                    FROM erp_applications 
                    WHERE branch_id = ? AND status = ? ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("is", $branch_id, $statusFilter);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        
        return $data;
    }

    /**
     * Fetch full details for a single application (For the Review Modal)
     */
    public function getSingleApplication($app_id, $branch_id)
    {
        $sql = "SELECT a.*, b.branch_name 
                FROM erp_applications a
                LEFT JOIN erp_branches b ON a.branch_id = b.branch_id
                WHERE a.app_id = ? AND a.branch_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $app_id, $branch_id);
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        $stmt->close();
        return $data;
    }

    /**
     * Update the Admission Status (Pending, Shortlisted, Selected)
     */
    public function updateStatus($app_id, $status, $branch_id)
    {
        $sql = "UPDATE erp_applications SET status = ? WHERE app_id = ? AND branch_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $status, $app_id, $branch_id);
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Update the Scholarship/Financial Aid Status
     */
    public function updateScholarship($app_id, $scholarship_status, $branch_id)
    {
        $sql = "UPDATE erp_applications SET scholarship_status = ? WHERE app_id = ? AND branch_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $scholarship_status, $app_id, $branch_id);
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    /**
     * Clean up the connection when the model is destroyed
     */
    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
}
?>