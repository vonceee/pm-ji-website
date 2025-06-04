<?php
/**
 * components/booking-actions.php
 */

$bookingId = $booking['id'];
$status = $booking['status'];
?>

<div class="btn-group" role="group">
    <!-- View Details Button - Always available -->
    <button class="btn btn-outline-info btn-sm" 
            onclick="viewBookingDetails(<?= htmlspecialchars(json_encode($booking)) ?>)"
            title="View Details">
        <i class="fas fa-eye"></i>
    </button>

    <?php if ($status === 'pending'): ?>
        <!-- Approve Button -->
        <button class="btn btn-outline-success btn-sm" 
                onclick="approveBooking(<?= $bookingId ?>)"
                title="Approve Booking">
            <i class="fas fa-check"></i>
        </button>
        
        <!-- Reject Button -->
        <button class="btn btn-outline-danger btn-sm" 
                onclick="rejectBooking(<?= $bookingId ?>)"
                title="Reject Booking">
            <i class="fas fa-times"></i>
        </button>
        
    <?php elseif ($status === 'approved'): ?>
        <!-- Complete Button -->
        <button class="btn btn-outline-primary btn-sm" 
                onclick="completeBooking(<?= $bookingId ?>)"
                title="Mark as Complete">
            <i class="fas fa-flag-checkered"></i>
        </button>
        
        <!-- Cancel Button -->
        <button class="btn btn-outline-warning btn-sm" 
                onclick="cancelBooking(<?= $bookingId ?>)"
                title="Cancel Booking">
            <i class="fas fa-ban"></i>
        </button>
        
    <?php endif; ?>

    <!-- Print Button - Always available -->
    <button class="btn btn-outline-secondary btn-sm" 
            onclick="printBooking(<?= $bookingId ?>)" 
            title="Print Receipt">
        <i class="fas fa-print"></i>
    </button>
</div>

