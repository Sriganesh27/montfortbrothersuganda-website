<?php
require_once __DIR__ . '/../includes/security.php';
require_once 'db_config.php'; // This file defines the $pdo object

$project_id = isset($_GET['project']) ? strtoupper(trim($_GET['project'])) : '';
if (!in_array($project_id, ['SSP001', 'CEP001'])) {
    http_response_code(400);
    echo "Invalid project ID";
    exit;
}

// Conversion rates
$conversion_rates = [
    'USD' => 3700,
    'EURO_GBP' => 4300,
    'UGX' => 1
];

function convertToUGX($amount, $currency, $rates) {
    return isset($rates[$currency]) ? $amount * $rates[$currency] : $amount;
}

/**
 * Helper to fetch donations using PDO
 */
function fetchProjectDonations($pdo, $project_id, $conversion_rates) {
    $donations = [];
    $total_ugx = 0;
    $error = null;

    try {
        // 1. Switched to $pdo and ensured table is web_donations
        $stmt = $pdo->prepare("SELECT receipt_number, full_name, is_anonymous, amount, currency, created_at 
                               FROM web_donations 
                               WHERE project_id = ? AND payment_status = 'success' 
                               ORDER BY created_at ASC");
        
        $stmt->execute([$project_id]);
        
        // 2. Switched to PDO fetch logic
        while ($row = $stmt->fetch()) {
            $amount_ugx = convertToUGX($row['amount'], $row['currency'], $conversion_rates);
            $row['amount_ugx'] = $amount_ugx;
            $donations[] = $row;
            $total_ugx += $amount_ugx;
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }

    return [$donations, $total_ugx, $error];
}

// Pass $pdo instead of $conn
list($donations, $total_ugx, $error) = fetchProjectDonations($pdo, $project_id, $conversion_rates);

// Targets
$ssp_target_ugx = 50000000;
$cep_target_ugx = 22000000;
$target_ugx = ($project_id === 'SSP001') ? $ssp_target_ugx : $cep_target_ugx;

// Dummy values
$students_helped = 0;
$students_target = ($project_id === 'SSP001') ? 100 : 1000;
$helped_fund = $total_ugx * 0.90;
$spent_ugx = 0;

$percent_received = ($target_ugx > 0) ? min(100, round(($total_ugx / $target_ugx) * 100)) : 0;
$percent_students = ($students_target > 0) ? min(100, round(($students_helped / $students_target) * 100)) : 0;
$percent_spent = ($helped_fund > 0) ? min(100, round(($spent_ugx / $helped_fund) * 100)) : 0;

ob_start();
?>
<div class="projectContainer" id="<?php echo $project_id; ?>">
    <header class="projectTitle">
        <span class="badge">Project ID : <?php echo $project_id; ?></span>
        <h4><?php echo ($project_id === 'SSP001') ? 'Scholarships - Help Educate 100 Students in 2026' : 'Community Empowerment - An Egg a Week'; ?></h4>
    </header>

    <div class="projectcontent">
        <div class="projectOverview">
            <div class="image">
                <img src="assets/Images/<?php echo ($project_id === 'SSP001') ? 'support-student.webp' : 'support-community.webp'; ?>" alt="Project Image">
            </div>
            <div class="projectDesc">
                <?php if ($project_id === 'SSP001'): ?>
                    <p>In Uganda, nearly every child starts primary school — but many never finish. While primary enrollment is about 99%, completion drops to nearly 50%, transition to secondary falls to 59%, and tertiary enrollment is just 4–6%.</p>
                    <span>The main barrier: Poverty, School costs.</span>
                    <div class="mission-box">
                        <p><strong>Our Mission:</strong> Break the cycle of poverty by providing scholarships for <strong>100 vulnerable students</strong> from primary to tertiary levels.</p>
                    </div>
                <?php else: ?>
                    <p>In many low-fee schools in Uganda, children receive maize porridge and beans for lunch. While this provides basic energy, it does not supply enough protein and essential nutrients needed for proper growth, concentration, and immunity.</p>
                    <span>The Challenge: Nutritional deficiency in low-budget school meals.</span>
                    <div class="mission-box">
                        <p><strong>Our Mission:</strong> The <strong>“An Egg a Week” initiative</strong> aims to provide one boiled egg per week to students in schools where annual mess fees are below <strong>UGX 270,000</strong>.</p>
                    </div>
                <?php endif; ?>
                <button class="donate-btn open-donate-window" 
                        data-purpose="<?php echo ($project_id === 'SSP001') ? 'Scholarships for student' : 'Community Empowerment'; ?>"
                        data-project-id="<?php echo $project_id; ?>"
                        data-project-name="<?php echo ($project_id === 'SSP001') ? 'Help Educate 100 Students in 2026' : 'An Egg a Week Initiative'; ?>">
                    Contribute now
                </button>
            </div>
            <div class="targetBar horizontal-bars">
                <div class="progress-wrapper">
                    <h5 class="bar-title">Overall Funding Progress</h5>
                    <div class="progress-labels">
                        <span>Received: <strong><?php echo number_format($total_ugx); ?> UGX</strong></span>
                        <span>Target: <strong><?php echo number_format($target_ugx); ?> UGX</strong></span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-fill received-fill" style="width: <?php echo $percent_received; ?>%;">
                            <span class="progress-text"><?php echo $percent_received; ?>%</span>
                        </div>
                    </div>
                </div>
                <div class="progress-wrapper">
                    <h5 class="bar-title"><?php echo ($project_id === 'SSP001') ? 'Student Impact Goal' : 'Weekly Student Reach'; ?></h5>
                    <div class="progress-labels">
                        <span><?php echo ($project_id === 'SSP001') ? 'Students Helped' : 'Current Beneficiaries'; ?>: <strong><?php echo $students_helped; ?></strong></span>
                        <span>Goal: <strong><?php echo $students_target; ?></strong></span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-fill students-fill" style="width: <?php echo $percent_students; ?>%;">
                            <span class="progress-text"><?php echo $percent_students; ?>%</span>
                        </div>
                    </div>
                </div>
                <div class="progress-wrapper">
                    <h5 class="bar-title"><?php echo ($project_id === 'SSP001') ? 'Transforming Donations into Aid' : 'Direct Food Support (90% of Received)'; ?></h5>
                    <div class="progress-labels">
                        <span>Spent: <strong><?php echo number_format($spent_ugx); ?> UGX</strong></span>
                        <span>Allocated Fund: <strong><?php echo number_format($helped_fund); ?> UGX</strong></span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-fill spent-fill" style="width: <?php echo $percent_spent; ?>%;">
                            <span class="progress-text"><?php echo $percent_spent; ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="projectDetails">
            <div class="projectdata">
                <h3>Project Description</h3>
                <div class="projecttext">
                    <p>Every contribution keeps a child in school and builds a stronger future.</p>
                </div>
                <div class="content-flex-column">
                    <?php if ($project_id === 'SSP001'): ?>
                        <div class="goal-card">
                            <h4>Target</h4>
                            <p>Provide scholarships for <strong>100 vulnerable students</strong> across primary, secondary, senior secondary, and tertiary levels.</p>
                            <span class="highlight-box">A partial scholarship of <strong>UGX 300,000</strong> keeps a child in school for a year.</span>
                        </div>
                        <div class="fund-card">
                            <h4>Budget</h4>
                            <table class="currency-table">
                                <thead><tr><th>Level</th><th>UGX</th><th>USD</th><th>EUR</th></tr></thead>
                                <tbody>
                                    <tr><td>Primary</td><td>10,000,000</td><td>$2,700</td><td>€2,500</td></tr>
                                    <tr><td>Secondary</td><td>12,000,000</td><td>$3,250</td><td>€3,000</td></tr>
                                    <tr><td>Senior Secondary</td><td>13,000,000</td><td>$3,500</td><td>€3,250</td></tr>
                                    <tr><td>Tertiary</td><td>15,000,000</td><td>$4,050</td><td>€3,750</td></tr>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="goal-card">
                            <h4>Target</h4>
                            <p>Provide <strong>one boiled egg per week</strong> to <strong>1,000 students</strong> in schools where annual mess fees are below <strong>UGX 270,000</strong>.</p>
                            <ul class="impact-stats" style="font-weight: bold; margin-left:10%">
                                <li class="stat-item"><strong>44</strong> <span>School weeks per year</span></li>
                                <li class="stat-item"><strong>44,000</strong> <span>Total eggs required annually</span></li>
                            </ul>
                            <span class="highlight-box">A gift of <strong>UGX 22,000</strong> provides one child with an egg every school week for a full year.</span>
                        </div>
                        <div class="fund-card">
                            <h4>Budget</h4>
                            <p>To provide one boiled egg per week to 1,000 students across 44 school weeks:</p>
                            <table class="currency-table">
                                <thead><tr><th>Metric</th><th>UGX</th><th>USD</th><th>EUR</th></tr></thead>
                                <tbody>
                                    <tr><td>Cost per Egg</td><td>500</td><td>$0.14</td><td>€0.12</td></tr>
                                    <tr><td><strong>Total Annual Cost</strong></td><td><strong>22,000,000</strong></td><td><strong>$5,950</strong></td><td><strong>€5,500</strong></td></tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="projectreport">
                <h3>Project Report</h3>
                <div class="projecttable fundman">
                    <div class="totalFund">
                        <p>Total fund received</p>
                        <span><?php echo number_format($total_ugx); ?> UGX</span>
                    </div>
                    <table>
                        <thead><tr><th colspan="4">Distribution of funds:</th></tr>
                        <tr><th>Category</th><th>Fund Allocated</th><th>Spent</th><th>Balance</th></tr></thead>
                        <tbody>
                            <?php if ($project_id === 'SSP001'): ?>
                                <tr><td>Pre-Primary</td><td>-</td><td>-</td><td>-</td></tr>
                                <tr><td>Primary</td><td>-</td><td>-</td><td>-</td></tr>
                                <tr><td>Secondary</td><td>-</td><td>-</td><td>-</td></tr>
                                <tr><td>Administrative Exp.</td><td>-</td><td>-</td><td>-</td></tr>
                                <tr class="total-row"><td colspan="3">Secured for the next year</td><td>-</td></tr>
                            <?php else: ?>
                                <tr><td>Amount for eggs</td><td>-</td><td>-</td><td>-</td></tr>
                                <tr><td>Administrative Exp.</td><td>-</td><td>-</td><td>-</td></tr>
                                <tr class="total-row"><td colspan="3">Secured for the next year</td><td>-</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="projecttable donors">
                    <?php if ($error): ?>
                        <div style="color: red; background:#ffeeee; padding:10px; border-radius:5px; margin-bottom:10px;">
                            <strong>Database error:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <table>
                        <thead><tr><th colspan="3">Donors of the Project</th></tr>
                        <tr><th>Donor Code</th><th>Name</th><th>Contributed (UGX)</th></tr></thead>
                        <tbody>
                            <?php if (count($donations) > 0): ?>
                                <?php foreach ($donations as $donation): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($donation['receipt_number'] ?? 'N/A'); ?></td>
                                        <td><?php echo $donation['is_anonymous'] ? 'Anonymous' : htmlspecialchars($donation['full_name']); ?></td>
                                        <td><?php echo number_format($donation['amount_ugx']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" style="text-align:center;">No donations yet. Be the first to support!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$html = ob_get_clean();
echo $html;
?>