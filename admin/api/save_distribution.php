<?php
// admin/api/save_distribution.php

declare(strict_types=1);

require_once '../../includes/security.php';

header('Content-Type: application/json; charset=UTF-8');

function sendJsonResponse(int $statusCode, array $payload): never
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function amountToMinorUnits(mixed $value, string $fieldName): int
{
    if (!is_int($value) && !is_float($value) && !is_string($value)) {
        throw new InvalidArgumentException($fieldName . ' must be a valid number.');
    }

    $rawValue = trim((string) $value);

    if ($rawValue === '' || !preg_match('/^\d+(?:\.\d{1,2})?$/', $rawValue)) {
        throw new InvalidArgumentException(
            $fieldName . ' must be a non-negative number with no more than 2 decimal places.'
        );
    }

    $amount = (float) $rawValue;

    if (!is_finite($amount) || $amount < 0) {
        throw new InvalidArgumentException($fieldName . ' cannot be negative.');
    }

    $minorUnits = (int) round($amount * 100);

    if ($minorUnits < 0) {
        throw new InvalidArgumentException($fieldName . ' is outside the supported range.');
    }

    return $minorUnits;
}

function minorUnitsToDatabaseAmount(int $minorUnits): string
{
    return number_format($minorUnits / 100, 2, '.', '');
}

// Only an authenticated administrator may save fund distributions.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    sendJsonResponse(401, [
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    sendJsonResponse(405, [
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
}

// Validate the CSRF token sent by admin/assets/js/distribution.js.
$sessionCsrfToken = (string) ($_SESSION['csrf_token'] ?? '');
$requestCsrfToken = trim((string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

if (
    $sessionCsrfToken === ''
    || $requestCsrfToken === ''
    || !hash_equals($sessionCsrfToken, $requestCsrfToken)
) {
    sendJsonResponse(403, [
        'success' => false,
        'message' => 'Invalid security token. Refresh the page and try again.'
    ]);
}

$contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
if ($contentType !== '' && !str_contains($contentType, 'application/json')) {
    sendJsonResponse(415, [
        'success' => false,
        'message' => 'The request must use application/json.'
    ]);
}

$rawBody = file_get_contents('php://input');
if ($rawBody === false || trim($rawBody) === '') {
    sendJsonResponse(400, [
        'success' => false,
        'message' => 'The request body is empty.'
    ]);
}

try {
    $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    sendJsonResponse(400, [
        'success' => false,
        'message' => 'Invalid JSON request.'
    ]);
}

if (!is_array($data)) {
    sendJsonResponse(400, [
        'success' => false,
        'message' => 'Invalid request data.'
    ]);
}

$donationId = filter_var(
    $data['donation_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($donationId === false) {
    sendJsonResponse(422, [
        'success' => false,
        'message' => 'A valid donation ID is required.'
    ]);
}

$allocations = $data['allocations'] ?? null;
if (!is_array($allocations)) {
    sendJsonResponse(422, [
        'success' => false,
        'message' => 'Allocation data is required.'
    ]);
}

// These rules must stay aligned with admin/api/get_donors.php.
$projectConfig = [
    'SSP001' => [
        'requires_students' => true,
        'requires_benefit_year' => true,
        'requires_terms' => true,
        'categories' => ['Scholarships', 'Sustainable Reserve']
    ],
    'CEP001' => [
        'requires_students' => true,
        'requires_benefit_year' => true,
        'requires_terms' => true,
        'categories' => ['Eggs Amount', 'Sustainable Reserve']
    ],
    'IDP001' => [
        'requires_students' => false,
        'requires_benefit_year' => false,
        'requires_terms' => false,
        'categories' => ['Materials & Labor', 'Solar & Water']
    ]
];

try {
    $studentsBenefited = filter_var(
        $data['students_benefited'] ?? 0,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0]]
    );

    if ($studentsBenefited === false) {
        throw new InvalidArgumentException('Students benefited must be zero or a positive whole number.');
    }

    $termsBenefited = filter_var(
        $data['terms_benefited'] ?? 0,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0, 'max_range' => 3]]
    );

    if ($termsBenefited === false) {
        throw new InvalidArgumentException('Terms benefited must be between 0 and 3.');
    }

    $benefitYear = null;
    $rawBenefitYear = $data['benefit_year'] ?? null;

    if ($rawBenefitYear !== null && $rawBenefitYear !== '') {
        $benefitYear = filter_var(
            $rawBenefitYear,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1900, 'max_range' => 2100]]
        );

        if ($benefitYear === false) {
            throw new InvalidArgumentException('Benefit Year must be a four-digit year between 1900 and 2100.');
        }
    }

    require_once '../../api/db_config.php';

    $pdo->beginTransaction();

    // Lock the donation while validating and saving to prevent concurrent over-allocation.
    $donationStmt = $pdo->prepare("
        SELECT
            id,
            project_id,
            amount_received,
            payment_status
        FROM web_donations
        WHERE id = ?
        FOR UPDATE
    ");
    $donationStmt->execute([$donationId]);
    $donation = $donationStmt->fetch(PDO::FETCH_ASSOC);

    if (!$donation) {
        throw new InvalidArgumentException('The selected donation does not exist.');
    }

    if (($donation['payment_status'] ?? '') !== 'success') {
        throw new InvalidArgumentException('Only successful donations can be distributed.');
    }

    $projectId = (string) ($donation['project_id'] ?? '');
    if (!isset($projectConfig[$projectId])) {
        throw new InvalidArgumentException('This donation does not belong to a supported project.');
    }

    $config = $projectConfig[$projectId];
    $allowedCategories = $config['categories'];

    // Project fields that do not apply are stored as zero/NULL.
    if (!$config['requires_students']) {
        $studentsBenefited = 0;
    }

    if (!$config['requires_benefit_year']) {
        $benefitYear = null;
    }

    if (!$config['requires_terms']) {
        $termsBenefited = 0;
    }

    $receivedMinorUnits = amountToMinorUnits(
        $donation['amount_received'] ?? '',
        'Received amount'
    );

    if ($receivedMinorUnits <= 0) {
        throw new InvalidArgumentException('The donation has no distributable received amount.');
    }

    // Initialize every allowed category to zero so omitted fields cannot leave stale values.
    $validatedAllocations = [];
    foreach ($allowedCategories as $allowedCategory) {
        $validatedAllocations[$allowedCategory] = 0;
    }

    $submittedCategories = [];

    foreach ($allocations as $index => $allocation) {
        if (!is_array($allocation)) {
            throw new InvalidArgumentException('Allocation row ' . ($index + 1) . ' is invalid.');
        }

        $category = trim((string) ($allocation['category'] ?? ''));

        if ($category === '' || !in_array($category, $allowedCategories, true)) {
            throw new InvalidArgumentException('An invalid allocation category was submitted.');
        }

        if (isset($submittedCategories[$category])) {
            throw new InvalidArgumentException('Duplicate allocation category: ' . $category . '.');
        }

        $submittedCategories[$category] = true;
        $validatedAllocations[$category] = amountToMinorUnits(
            $allocation['amount'] ?? '',
            $category . ' amount'
        );
    }

    $totalAllocatedMinorUnits = array_sum($validatedAllocations);

    if ($totalAllocatedMinorUnits > $receivedMinorUnits) {
        throw new InvalidArgumentException(
            'Financial error: Allocated amount exceeds the total received UGX.'
        );
    }

    $updateDonationStmt = $pdo->prepare("
        UPDATE web_donations
        SET students_benefited = ?,
            benefit_year = ?,
            terms_benefited = ?
        WHERE id = ?
    ");
    $updateDonationStmt->execute([
        $studentsBenefited,
        $benefitYear,
        $termsBenefited,
        $donationId
    ]);

    $saveDistributionStmt = $pdo->prepare("
        INSERT INTO web_donor_distributions
            (donation_id, category_name, amount_allocated)
        VALUES
            (?, ?, ?)
        ON DUPLICATE KEY UPDATE
            amount_allocated = VALUES(amount_allocated)
    ");

    foreach ($validatedAllocations as $category => $minorUnits) {
        $saveDistributionStmt->execute([
            $donationId,
            $category,
            minorUnitsToDatabaseAmount($minorUnits)
        ]);
    }

    $pdo->commit();

    sendJsonResponse(200, [
        'success' => true,
        'message' => 'Fund distribution saved successfully.',
        'data' => [
            'donation_id' => $donationId,
            'project_id' => $projectId,
            'students_benefited' => $studentsBenefited,
            'benefit_year' => $benefitYear,
            'terms_benefited' => $termsBenefited,
            'total_allocated' => minorUnitsToDatabaseAmount($totalAllocatedMinorUnits),
            'remaining_amount' => minorUnitsToDatabaseAmount(
                $receivedMinorUnits - $totalAllocatedMinorUnits
            )
        ]
    ]);
} catch (InvalidArgumentException $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    sendJsonResponse(422, [
        'success' => false,
        'message' => $exception->getMessage()
    ]);
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Save distribution API error: ' . $exception->getMessage());

    sendJsonResponse(500, [
        'success' => false,
        'message' => 'Unable to save the fund distribution.'
    ]);
}