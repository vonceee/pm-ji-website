<?php
/**
 * admin_cancellations.php
 * 
 * Admin interface to manage booking cancellations and process refunds
 * Path: /NEW-PM-JI-RESERVIFY/pages/admin/cancellations.php
 */

session_start();

// Check admin authentication (adjust according to your admin auth system)
/*
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /NEW-PM-JI-RESERVIFY/pages/admin/index.php');
    exit;
}
    */

require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';
use Config\Database;

$pdo = Database::getConnection();

// Handle refund processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'process_refund') {
            $cancellationId = (int) $_POST['cancellation_id'];
            $refundAmount = (float) $_POST['refund_amount'];
            $adminNotes = trim($_POST['admin_notes']);

            $stmt = $pdo->prepare('
                UPDATE tbl_cancellations 
                SET refund_status = "processed", 
                    refund_amount = :refund_amount,
                    refund_processed_at = NOW(),
                    admin_notes = :admin_notes,
                    updated_at = NOW()
                WHERE id = :id
            ');

            $stmt->execute([
                ':refund_amount' => $refundAmount,
                ':admin_notes' => $adminNotes,
                ':id' => $cancellationId
            ]);

            $success_message = "Refund processed successfully!";
        } elseif ($_POST['action'] === 'update_refund_status') {
            $cancellationId = (int) $_POST['cancellation_id'];
            $refundStatus = $_POST['refund_status'];
            $adminNotes = trim($_POST['admin_notes']);

            $stmt = $pdo->prepare('
                UPDATE tbl_cancellations 
                SET refund_status = :refund_status,
                    admin_notes = :admin_notes,
                    updated_at = NOW()
                WHERE id = :id
            ');

            $stmt->execute([
                ':refund_status' => $refundStatus,
                ':admin_notes' => $adminNotes,
                ':id' => $cancellationId
            ]);

            $success_message = "Refund status updated successfully!";
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Fetch cancellations with booking and user details
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = '1=1';
$params = [];

if ($filter_status) {
    $where .= ' AND c.refund_status = :filter_status';
    $params[':filter_status'] = $filter_status;
}

if ($search) {
    $where .= ' AND (b.reference_number LIKE :search OR u.email LIKE :search OR b.event_type LIKE :search)';
    $params[':search'] = "%{$search}%";
}

// Count total cancellations
$countSql = "
    SELECT COUNT(*) 
    FROM tbl_cancellations c
    JOIN tbl_bookings b ON c.booking_id = b.id
    JOIN tbl_users u ON c.user_id = u.id
    WHERE {$where}
";

$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalCancellations = (int) $stmtCount->fetchColumn();

// Fetch cancellations data
$dataSql = "
    SELECT 
        c.*,
        b.reference_number,
        b.event_type,
        b.reservation_date,
        b.start_time,
        b.end_time,
        u.email as user_email,
        u.first_name,
        u.last_name,
        p.amount_paid,
        p.payment_method,
        p.payment_type
    FROM tbl_cancellations c
    JOIN tbl_bookings b ON c.booking_id = b.id
    JOIN tbl_users u ON c.user_id = u.id
    LEFT JOIN tbl_payments p ON b.id = p.booking_id
    WHERE {$where}
    ORDER BY c.cancelled_at DESC
    LIMIT :limit OFFSET :offset
";

$params[':limit'] = $limit;
$params[':offset'] = $offset;

$stmtData = $pdo->prepare($dataSql);
$stmtData->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);

foreach ($params as $key => $value) {
    if (in_array($key, [':limit', ':offset'], true))
        continue;
    $stmtData->bindValue($key, $value);
}

$stmtData->execute();
$cancellations = $stmtData->fetchAll(PDO::FETCH_ASSOC);

$totalPages = ceil($totalCancellations / $limit);

function getRefundStatusBadge($status)
{
    $badges = [
        'pending' => 'warning',
        'processed' => 'success',
        'failed' => 'danger'
    ];
    return $badges[$status] ?? 'secondary';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cancellations - Admin Panel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-ban"></i> Manage Cancellations</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/NEW-PM-JI-RESERVIFY/admin/">Dashboard</a></li>
                            <li class="breadcrumb-item active">Cancellations</li>
                        </ol>
                    </nav>
                </div>

                <!-- Success/Error Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="form-inline">
                            <div class="form-group mr-3">
                                <label class="sr-only" for="search">Search</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="search" name="search"
                                        placeholder="Search by reference, email, or event..."
                                        value="<?= htmlspecialchars($search) ?>">
                                </div>
                            </div>

                            <div class="form-group mr-3">
                                <select class="form-control" name="filter_status">
                                    <option value="">All Refund Status</option>
                                    <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending
                                    </option>
                                    <option value="processed" <?= $filter_status === 'processed' ? 'selected' : '' ?>>
                                        Processed</option>
                                    <option value="failed" <?= $filter_status === 'failed' ? 'selected' : '' ?>>Failed
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>

                            <?php if ($search || $filter_status): ?>
                                <a href="?" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Cancellations Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Cancellations
                            <span class="badge badge-secondary"><?= $totalCancellations ?> total</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($cancellations) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Booking Details</th>
                                            <th>Customer</th>
                                            <th>Cancellation Info</th>
                                            <th>Refund Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cancellations as $cancellation): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($cancellation['event_type']) ?></strong><br>
                                                    <small class="text-muted">
                                                        Ref: <?= htmlspecialchars($cancellation['reference_number']) ?><br>
                                                        Date:
                                                        <?= date('M j, Y', strtotime($cancellation['reservation_date'])) ?><br>
                                                        Time: <?= date('g:i A', strtotime($cancellation['start_time'])) ?> -
                                                        <?= date('g:i A', strtotime($cancellation['end_time'])) ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($cancellation['first_name'] . ' ' . $cancellation['last_name']) ?></strong><br>
                                                    <small
                                                        class="text-muted"><?= htmlspecialchars($cancellation['user_email']) ?></small>
                                                </td>
                                                <td>
                                                    <small>
                                                        <strong>Cancelled:</strong>
                                                        <?= date('M j, Y g:i A', strtotime($cancellation['cancelled_at'])) ?><br>
                                                        <strong>Reason:</strong>
                                                        <?= htmlspecialchars($cancellation['reason']) ?><br>
                                                        <?php if ($cancellation['amount_paid']): ?>
                                                            <strong>Paid:</strong>
                                                            ₱<?= number_format($cancellation['amount_paid'], 2) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-<?= getRefundStatusBadge($cancellation['refund_status']) ?>">
                                                        <?= ucfirst($cancellation['refund_status']) ?>
                                                    </span>
                                                    <?php if ($cancellation['refund_amount']): ?>
                                                        <br><small>₱<?= number_format($cancellation['refund_amount'], 2) ?></small>
                                                    <?php endif; ?>
                                                    <?php if ($cancellation['refund_processed_at']): ?>
                                                        <br><small class="text-muted">
                                                            Processed:
                                                            <?= date('M j, Y', strtotime($cancellation['refund_processed_at'])) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info mb-1 view-details-btn"
                                                        data-cancellation='<?= json_encode($cancellation) ?>'>
                                                        <i class="fas fa-eye"></i> Details
                                                    </button>

                                                    <?php if ($cancellation['refund_status'] === 'pending'): ?>
                                                        <button class="btn btn-sm btn-success mb-1 process-refund-btn"
                                                            data-cancellation='<?= json_encode($cancellation) ?>'>
                                                            <i class="fas fa-money-bill"></i> Process
                                                        </button>
                                                    <?php endif; ?>

                                                    <button class="btn btn-sm btn-warning mb-1 update-status-btn"
                                                        data-cancellation='<?= json_encode($cancellation) ?>'>
                                                        <i class="fas fa-edit"></i> Update
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <div class="card-footer">
                                    <nav aria-label="Cancellations pagination">
                                        <ul class="pagination justify-content-center mb-0">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&filter_status=<?= urlencode($filter_status) ?>">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                                    <a class="page-link"
                                                        href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&filter_status=<?= urlencode($filter_status) ?>">
                                                        <?= $i ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link"
                                                        href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&filter_status=<?= urlencode($filter_status) ?>">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No cancellations found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Refund Modal -->
    <div class="modal fade" id="processRefundModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-money-bill"></i> Process Refund
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="process_refund">
                        <input type="hidden" name="cancellation_id" id="process_cancellation_id">

                        <div id="process_booking_details"></div>

                        <div class="form-group">
                            <label for="process_refund_amount">Refund Amount</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₱</span>
                                </div>
                                <input type="number" class="form-control" id="process_refund_amount"
                                    name="refund_amount" step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="process_admin_notes">Admin Notes</label>
                            <textarea class="form-control" id="process_admin_notes" name="admin_notes" rows="3"
                                placeholder="Add any notes about this refund..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Process Refund
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-edit"></i> Update Refund Status
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_refund_status">
                        <input type="hidden" name="cancellation_id" id="update_cancellation_id">

                        <div id="update_booking_details"></div>

                        <div class="form-group">
                            <label for="update_refund_status">Refund Status</label>
                            <select class="form-control" id="update_refund_status" name="refund_status" required>
                                <option value="pending">Pending</option>
                                <option value="processed">Processed</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="update_admin_notes">Admin Notes</label>
                            <textarea class="form-control" id="update_admin_notes" name="admin_notes" rows="3"
                                placeholder="Add any notes about this status update..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="viewDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle"></i> Cancellation Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="details_modal_content">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Process Refund Modal
            $('.process-refund-btn').click(function () {
                const cancellation = JSON.parse($(this).attr('data-cancellation'));

                $('#process_cancellation_id').val(cancellation.id);
                $('#process_refund_amount').val(cancellation.amount_paid || 0);

                $('#process_booking_details').html(`
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Booking Information</h6>
                        <p><strong>Event:</strong> ${cancellation.event_type}</p>
                        <p><strong>Reference:</strong> ${cancellation.reference_number}</p>
                        <p><strong>Customer:</strong> ${cancellation.first_name} ${cancellation.last_name}</p>
                        <p><strong>Email:</strong> ${cancellation.user_email}</p>
                        <p><strong>Amount Paid:</strong> ₱${parseFloat(cancellation.amount_paid || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}</p>
                        <p><strong>Cancellation Reason:</strong> ${cancellation.reason}</p>
                    </div>
                </div>
            `);

                $('#processRefundModal').modal('show');
            });

            // Update Status Modal
            $('.update-status-btn').click(function () {
                const cancellation = JSON.parse($(this).attr('data-cancellation'));

                $('#update_cancellation_id').val(cancellation.id);
                $('#update_refund_status').val(cancellation.refund_status);
                $('#update_admin_notes').val(cancellation.admin_notes || '');

                $('#update_booking_details').html(`
                <div class="card mb-3">
                    <div class="card-body">
                        <h6>Booking Information</h6>
                        <p><strong>Event:</strong> ${cancellation.event_type}</p>
                        <p><strong>Reference:</strong> ${cancellation.reference_number}</p>
                        <p><strong>Customer:</strong> ${cancellation.first_name} ${cancellation.last_name}</p>
                        <p><strong>Current Status:</strong> <span class="badge badge-${getStatusBadgeClass(cancellation.refund_status)}">${cancellation.refund_status.charAt(0).toUpperCase() + cancellation.refund_status.slice(1)}</span></p>
                    </div>
                </div>
            `);

                $('#updateStatusModal').modal('show');
            });

            // View Details Modal
            $('.view-details-btn').click(function () {
                const cancellation = JSON.parse($(this).attr('data-cancellation'));

                let detailsHtml = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-calendar"></i> Booking Details</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Reference Number:</strong> ${cancellation.reference_number}</p>
                                <p><strong>Event Type:</strong> ${cancellation.event_type}</p>
                                <p><strong>Date:</strong> ${new Date(cancellation.reservation_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                                <p><strong>Time:</strong> ${formatTime(cancellation.start_time)} - ${formatTime(cancellation.end_time)}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-user"></i> Customer Details</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Name:</strong> ${cancellation.first_name} ${cancellation.last_name}</p>
                                <p><strong>Email:</strong> ${cancellation.user_email}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-ban"></i> Cancellation Details</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Cancelled At:</strong> ${new Date(cancellation.cancelled_at).toLocaleString()}</p>
                                <p><strong>Reason:</strong> ${cancellation.reason}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="fas fa-money-bill"></i> Payment & Refund Details</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Amount Paid:</strong> ₱${parseFloat(cancellation.amount_paid || 0).toLocaleString('en-US', { minimumFractionDigits: 2 })}</p>
                                <p><strong>Payment Method:</strong> ${cancellation.payment_method || 'N/A'}</p>
                                <p><strong>Refund Status:</strong> <span class="badge badge-${getStatusBadgeClass(cancellation.refund_status)}">${cancellation.refund_status.charAt(0).toUpperCase() + cancellation.refund_status.slice(1)}</span></p>
            `;

                if (cancellation.refund_amount) {
                    detailsHtml += `<p><strong>Refund Amount:</strong> ₱${parseFloat(cancellation.refund_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</p>`;
                }

                if (cancellation.refund_processed_at) {
                    detailsHtml += `<p><strong>Refund Processed:</strong> ${new Date(cancellation.refund_processed_at).toLocaleString()}</p>`;
                }

                detailsHtml += `
                            </div>
                        </div>
                    </div>
                </div>
            `;

                if (cancellation.admin_notes) {
                    detailsHtml += `
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6><i class="fas fa-sticky-note"></i> Admin Notes</h6>
                                </div>
                                <div class="card-body">
                                    <p>${cancellation.admin_notes}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                }

                $('#details_modal_content').html(detailsHtml);
                $('#viewDetailsModal').modal('show');
            });

            // Helper functions
            function getStatusBadgeClass(status) {
                switch (status) {
                    case 'pending': return 'warning';
                    case 'processed': return 'success';
                    case 'failed': return 'danger';
                    default: return 'secondary';
                }
            }

            function formatTime(timeString) {
                if (!timeString) return 'N/A';
                const date = new Date('2000-01-01 ' + timeString);
                return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            }

            // Auto-dismiss alerts after 5 seconds
            setTimeout(function () {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>