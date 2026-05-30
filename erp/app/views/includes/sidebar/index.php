<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="sidebar" id="sidebar">
    <ul>
    <li>
        <a href="index.php">
        <button class="main-item" data-menu="dashboard">
        <i class="fa fa-home"></i>
        <span>Dashboard</span></button>
        </a>
    </li>
    <li>
        <button class="main-item" data-menu="student">
        <i class="fa fa-users"></i>
        <span>Student Management</span>
        </button>
        <ul class="sub-menu">
        
        <a href="#manage_applications" class="main-item"></i> <span>Applications</span>
</a>  
        
        <li><a href="#admission" id="addstudent">Add Student</a></li>
        <li><a href="#list" id="viewstudent">View Students</a></li>
        <li><a href="#summary" id="summary">Students Summary</a></li>
        <li><a href="#migrate" id="migrate">Migrate</a></li>
        <li><a href="#quickedit" id="quickedit">Quick Edit</a></li>
        <li><a href="#academicedit" id="academicedit">Academic Edit</a></li>
        <li><a href="#searchstudent" id="searchstudent">Search Student</a></li>
        <li><a href="#marks" id="studentmarks">Student Marks</a></li>
        <li><a href="#studentphotos" id="studentphotos">Student Photos</a></li>
        <li><a href="#studentaccounts" id="studentaccounts">Student Accounts</a></li>
        <li><a href="#oldstudentdebt" id="oldstudentdebt">Old Students Debts</a></li>
        <li><a href="#oldstudentrec" id="oldstudentrec">Old Student Receipts</a></li>
        
        </ul>
    </li>
    <li>
        <button class="main-item" data-menu="fees">
        <i class="fa fa-money-check"></i>
        <span>Fee Management</span>
        </button>
        <ul class="sub-menu">
        <li><a href="#" id="feescate">Fees Categories</a></li>
        <li><a href="#" id="payfees">Pay Fees</a></li>
        <li><a href="#" id="feesbreakdown">Fees Break Downs</a></li>
        <li><a href="#" id="viewpayments">View Fees Payments</a></li>
        <li><a href="#" id="onlinepayments">View Online Payments</a></li>
        <li><a href="#" id="pendingonlinepayments">Pending Online Payments</a></li>
        <li><a href="#" id="searchreceipt">Search Receipt</a></li>
        <li><a href="#" id="feestructure">Fee Structure</a></li>
        <li><a href="#" id="prev-bal">Previous Balances</a></li>
        <li><a href="#" id="fees-add">Fees Additions</a></li>
        <li><a href="#" id="fees-ded">Fees Deductions</a></li>
        <li><a href="#" id="bursary-cat">Bursary Categories</a></li>
        <li><a href="#" id="fees-burs">Fees Bursaries</a></li>
        <li><a href="#" id="feesreceipt">Fees Receipts</a></li>
        <li><a href="#" id="demandnotes">Fees Demand Notes</a></li>
        </ul>
    </li>
    <li>
        <button class="main-item" data-menu="expenses">
        <i class="fa fa-coins"></i>
        <span>Manage Expenses</span>
        </button>
        <ul class="sub-menu">
        <li><a href="#" id="newrequi">New Requisition</a></li>
        <li><a href="#" id="viewrequi">View Requisitions</a></li>
        <li><a href="#" id="expensecate">Expense Categories</a></li>
        <li><a href="#" id="recordexpense">Record Expense</a></li>
        <li><a href="#" id="viewexpense">View Expenses</a></li>
        <li><a href="#" id="viewcreditors">View Creditors</a></li>
        <li><a href="#" id="viewsuppliers">View Suppliers</a></li>
        </ul>
    </li>
    <li>
        <button class="main-item" data-menu="revenue">
        <i class="fa fa-donate"></i>
        <span>Manage Revenues</span>
        </button>
        <ul class="sub-menu">
        <li><a href="#" id="revenuecale">Revenue Categories</a></li>
        <li><a href="#" id="revenuesour">Revenue Sources</a></li>
        <li><a href="#" id="viewrevenue">View Revenues</a></li>
        </ul>
    </li>
    <li>
        <button class="main-item" data-menu="OLevel">
        <i class="fa fa-book"></i>
        <span>O Level</span>
        </button>
        <ul class="sub-menu">
        <li><a href="#" id="addexammarks">Add Exam Marks</a></li>
        <li><a href="#" id="addstaffmarks">Add Staff Marks</a></li>
        <li><a href="#" id="AOIreportcard">AOI Report Cards</a></li>
        <li><a href="#" id="EOTreportcard">EOT Report Cards</a></li>
        <li><a href="#" id="reportcard">Report Cards (100%)</a></li>
        <li><a href="#" id="CBCreportcard">CBC Report Cards</a></li>
        <li><a href="#" id="viewmarks">View Marks (/100)</a></li>
        <li><a href="#" id="VDreportcard">VD Report Cards</a></li>
        <li><a href="#" id="subjectmarksheets">Subject Mark Sheets</a></li>
        <li><a href="#" id="classmissingmarks">Class Missing Marks</a></li>
        <li><a href="#" id="setmissingmarks">Set Missing Marks</a></li>
        <li><a href="#" id="subjectanalysisgrade">Subject Analysis By Grade</a></li>
        <li><a href="#" id="subjectanalysisIDF">Subject Analysis By IDF</a></li>
        <li><a href="#" id="competences">Manage Competences</a></li>
        <li><a href="#" id="consideredsets">Considered Sets</a></li>
        <li><a href="#" id="optionalsubjects">Optional Subjects</a></li>
        <li><a href="#" id="addexammarksnew">Add Exam Marks New</a></li>
        <li><a href="#studentcomments" id="studentcomments">Student Comments</a></li>
        <li><a href="#projectevaluation" id="projectevaluation">Projects Evaluation</a></li>
        <li><a href="#NCDCreportcard" id="NCDCreportcard">NCDC Report Card</a></li>
        <li><a href="#reportcomments" id="reportcomments">Report Comments</a></li>
        </ul>
    </li>
    <li>
        <button class="main-item" data-menu="staff">
        <i class="fa fa-user-tie"></i>
        <span>Staff Management</span>
        </button>
        <ul class="sub-menu">
        <li><a href="#" id="staffposition">Staff Position</a></li>
        <li><a href="#" id="viewstaff">View Staff</a></li>
        <li><a href="#" id="staffadvances">Staff Advances</a></li>
        <li><a href="#" id="deductions">Deductions</a></li>
        <li><a href="#" id="staffpayments">Staff Payments</a></li>
        <li><a href="#" id="salaryledger">Salary Ledger</a></li>
        <li><a href="#" id="staffsubjects">Staff Subjects</a></li>
        </ul>
    </li>
    <li>
        <button class="main-item" data-menu="notification">
        <i class="fa fa-bell"></i>
        <span>Notification Center</span></button>
        <ul class="sub-menu">
        <li><a href="#" id="sms">SMS Notifications</a></li>
        <li><a href="#" id="email">Email Notifications</a></li>
        </ul>
    </li>
    <li>
        <button class="main-item" data-menu="assets">
        <i class="fa fa-address-card"></i>
        <span>Manage Assets</span>
        </button>
        <ul class="sub-menu">
        <li><a href="#" id="assetcate">Asset Categories</a></li>
        <li><a href="#" id="assetlocation">Asset Locations</a></li>
        <li><a href="#" id="viewasset">View Asset</a></li>
        <li><a href="#" id="assetspurchases">Assets Purchases</a></li>
        <li><a href="#" id="assetregister">Asset Register</a></li>
        </ul>
    </li>
    <li>
        <button class="main-item" data-menu="vistors">
        <i class="fa fa-address-card"></i>
        <span>Visitors Book</span>
        </button>
        <ul class="sub-menu">
        <li><a href="#" id="viewvisitors">View Visitors</a></li>
        <li><a href="#" id="visitorsreport">Visitors Report</a></li>
        </ul>
    </li>
    <li>
        <button class="main-item" data-menu="attendance">
        <i class="fa fa-address-card"></i>
        <span>Manage Attendance</span>
        </button>
        <ul class="sub-menu">
        <li><a href="#" id="studentatte">Student Attendance</a></li>
        <li><a href="#" id="staffatte">Staff Attendance</a></li>
        <li><a href="#" id="studentsummaries">Students Summaries</a></li>
        <li><a href="#" id="staffsummaries">Staff Summaries</a></li>
        </ul>
    </li>
    <li>
        <button class="main-item" data-menu="events">
        <i class="fa fa-calendar-alt"></i>
        <span>Events</span>
        </button>
        <ul class="sub-menu">
        <li><a href="#" id="upcomingevents">Upcoming Events</a></li>
        <li><a href="#" id="eventreport">Events Report</a></li>
        </ul>
    </li>
    <li>
    <a href="logout.php" style="text-decoration: none; color: inherit;">
        <button class="main-item" data-menu="logout">
            <i class="fa fa-power-off"></i>
            <span>Log Out</span>
        </button>
    </a>
</li>
    </ul>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
