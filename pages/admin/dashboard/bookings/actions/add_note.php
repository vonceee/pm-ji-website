<?php
// add_note.php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/NEW-PM-JI-RESERVIFY/config/database.php';

use Config\Database;

// Set content type to JSON
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate required fields
if (!isset($_POST['booking_id']) || !isset($_POST['note']) || !isset($_POST['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$booking_id = intval($_POST['booking_id']);
$note = trim($_POST['note']);
$action = trim($_POST['action']);
$admin_username = $_SESSION['admin_username'];
$note_type = isset($_POST['note_type']) ? trim($_POST['note_type']) : 'admin';

// Validate action
if ($action !== 'add_note') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action provided']);
    exit;
}

// Validate note content
if (empty($note)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Note cannot be empty']);
    exit;
}

// Validate note length (prevent extremely long notes)
if (strlen($note) > 1000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Note is too long (maximum 1000 characters)']);
    exit;
}

// Validate note type
$allowed_note_types = ['admin', 'system', 'customer', 'internal'];
if (!in_array($note_type, $allowed_note_types)) {
    $note_type = 'admin'; // Default to admin if invalid type provided
}

try {
    $pdo = Database::getConnection();

    // First, verify that the booking exists
    $checkStmt = $pdo->prepare("SELECT id, reference_id, user_id FROM tbl_bookings WHERE id = ?");
    $checkStmt->execute([$booking_id]);
    $booking = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }

    // Insert the note
    $insertStmt = $pdo->prepare("
        INSERT INTO tbl_booking_notes (
            booking_id,
            note_type,
            note_content,
            created_by,
            created_at,
            is_visible_to_customer
        ) VALUES (?, ?, ?, ?, NOW(), ?)
    ");

    // Determine if note should be visible to customer
    $visible_to_customer = ($note_type === 'customer') ? 1 : 0;

    $result = $insertStmt->execute([
        $booking_id,
        $note_type,
        $note,
        $admin_username,
        $visible_to_customer
    ]);

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Failed to add note']);
        exit;
    }

    $note_id = $pdo->lastInsertId();

    // Log the note addition
    $logStmt = $pdo->prepare("
        INSERT INTO tbl_booking_logs (
            booking_id,
            action,
            description,
            performed_by,
            created_at
        ) VALUES (?, 'note_added', ?, ?, NOW())
    ");

    $log_description = "Added {$note_type} note: " . substr($note, 0, 100) . (strlen($note) > 100 ? '...' : '');
    $logStmt->execute([$booking_id, $log_description, $admin_username]);

    // If note is visible to customer, create a notification
    if ($visible_to_customer) {
        createCustomerNotification($pdo, $booking['user_id'], $booking_id, $booking['reference_id']);
    }

    // Get the formatted note for response
    $noteStmt = $pdo->prepare("
        SELECT 
            id,
            note_type,
            note_content,
            created_by,
            created_at,
            is_visible_to_customer
        FROM tbl_booking_notes 
        WHERE id = ?
    ");
    $noteStmt->execute([$note_id]);
    $savedNote = $noteStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Note added successfully',
        'note' => [
            'id' => $savedNote['id'],
            'type' => $savedNote['note_type'],
            'content' => $savedNote['note_content'],
            'created_by' => $savedNote['created_by'],
            'created_at' => $savedNote['created_at'],
            'formatted_date' => date('M j, Y g:i A', strtotime($savedNote['created_at'])),
            'is_visible_to_customer' => $savedNote['is_visible_to_customer']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Database error in add_note.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("General error in add_note.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred']);
}

// Helper function to create customer notification
function createCustomerNotification($pdo, $user_id, $booking_id, $reference_id)
{
    try {
        $notifStmt = $pdo->prepare("
            INSERT INTO tbl_notifications (
                user_id,
                booking_id,
                type,
                title,
                message,
                created_at
            ) VALUES (?, ?, 'note', ?, ?, NOW())
        ");

        $title = 'New Note Added to Your Booking';
        $message = "A new note has been added to your booking #{$reference_id}. Please check your booking details for more information.";

        $notifStmt->execute([$user_id, $booking_id, $title, $message]);

    } catch (Exception $e) {
        error_log("Error creating customer notification: " . $e->getMessage());
    }
}

// Additional endpoint to get all notes for a booking
if (isset($_GET['get_notes']) && isset($_GET['booking_id'])) {
    try {
        $booking_id = intval($_GET['booking_id']);

        $notesStmt = $pdo->prepare("
            SELECT 
                id,
                note_type,
                note_content,
                created_by,
                created_at,
                is_visible_to_customer
            FROM tbl_booking_notes 
            WHERE booking_id = ? 
            ORDER BY created_at DESC
        ");
        $notesStmt->execute([$booking_id]);
        $notes = $notesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Format notes for display
        $formatted_notes = [];
        foreach ($notes as $note) {
            $formatted_notes[] = [
                'id' => $note['id'],
                'type' => $note['note_type'],
                'content' => $note['note_content'],
                'created_by' => $note['created_by'],
                'created_at' => $note['created_at'],
                'formatted_date' => date('M j, Y g:i A', strtotime($note['created_at'])),
                'is_visible_to_customer' => $note['is_visible_to_customer']
            ];
        }

        echo json_encode([
            'success' => true,
            'notes' => $formatted_notes
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error retrieving notes']);
    }
    exit;
}

// Additional endpoint to delete a note
if (isset($_POST['delete_note']) && isset($_POST['note_id'])) {
    try {
        $note_id = intval($_POST['note_id']);

        // Check if note exists and belongs to a valid booking
        $checkStmt = $pdo->prepare("
            SELECT bn.id, bn.booking_id, bn.note_content, b.reference_id 
            FROM tbl_booking_notes bn
            JOIN tbl_bookings b ON bn.booking_id = b.id
            WHERE bn.id = ?
        ");
        $checkStmt->execute([$note_id]);
        $note = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$note) {
            echo json_encode(['success' => false, 'message' => 'Note not found']);
            exit;
        }

        // Delete the note
        $deleteStmt = $pdo->prepare("DELETE FROM tbl_booking_notes WHERE id = ?");
        $result = $deleteStmt->execute([$note_id]);

        if ($result) {
            // Log the deletion
            $logStmt = $pdo->prepare("
                INSERT INTO tbl_booking_logs (
                    booking_id,
                    action,
                    description,
                    performed_by,
                    created_at
                ) VALUES (?, 'note_deleted', ?, ?, NOW())
            ");

            $log_description = "Deleted note: " . substr($note['note_content'], 0, 100) . (strlen($note['note_content']) > 100 ? '...' : '');
            $logStmt->execute([$note['booking_id'], $log_description, $admin_username]);

            echo json_encode([
                'success' => true,
                'message' => 'Note deleted successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete note']);
        }

    } catch (Exception $e) {
        error_log("Error deleting note: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error deleting note']);
    }
    exit;
}

// Additional endpoint to update a note
if (isset($_POST['update_note']) && isset($_POST['note_id']) && isset($_POST['updated_content'])) {
    try {
        $note_id = intval($_POST['note_id']);
        $updated_content = trim($_POST['updated_content']);

        // Validate updated content
        if (empty($updated_content)) {
            echo json_encode(['success' => false, 'message' => 'Updated note content cannot be empty']);
            exit;
        }

        if (strlen($updated_content) > 1000) {
            echo json_encode(['success' => false, 'message' => 'Updated note is too long (maximum 1000 characters)']);
            exit;
        }

        // Check if note exists and get original content for logging
        $checkStmt = $pdo->prepare("
            SELECT bn.id, bn.booking_id, bn.note_content, b.reference_id 
            FROM tbl_booking_notes bn
            JOIN tbl_bookings b ON bn.booking_id = b.id
            WHERE bn.id = ?
        ");
        $checkStmt->execute([$note_id]);
        $note = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$note) {
            echo json_encode(['success' => false, 'message' => 'Note not found']);
            exit;
        }

        // Update the note
        $updateStmt = $pdo->prepare("
            UPDATE tbl_booking_notes 
            SET note_content = ?, updated_at = NOW(), updated_by = ?
            WHERE id = ?
        ");
        $result = $updateStmt->execute([$updated_content, $admin_username, $note_id]);

        if ($result) {
            // Log the update
            $logStmt = $pdo->prepare("
                INSERT INTO tbl_booking_logs (
                    booking_id,
                    action,
                    description,
                    performed_by,
                    created_at
                ) VALUES (?, 'note_updated', ?, ?, NOW())
            ");

            $log_description = "Updated note from: '" . substr($note['note_content'], 0, 50) . "...' to: '" . substr($updated_content, 0, 50) . "...'";
            $logStmt->execute([$note['booking_id'], $log_description, $admin_username]);

            // Get the updated note for response
            $updatedNoteStmt = $pdo->prepare("
                SELECT 
                    id,
                    note_type,
                    note_content,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at,
                    is_visible_to_customer
                FROM tbl_booking_notes 
                WHERE id = ?
            ");
            $updatedNoteStmt->execute([$note_id]);
            $updatedNote = $updatedNoteStmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'message' => 'Note updated successfully',
                'note' => [
                    'id' => $updatedNote['id'],
                    'type' => $updatedNote['note_type'],
                    'content' => $updatedNote['note_content'],
                    'created_by' => $updatedNote['created_by'],
                    'created_at' => $updatedNote['created_at'],
                    'updated_by' => $updatedNote['updated_by'],
                    'updated_at' => $updatedNote['updated_at'],
                    'formatted_date' => date('M j, Y g:i A', strtotime($updatedNote['created_at'])),
                    'formatted_updated_date' => $updatedNote['updated_at'] ? date('M j, Y g:i A', strtotime($updatedNote['updated_at'])) : null,
                    'is_visible_to_customer' => $updatedNote['is_visible_to_customer']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update note']);
        }

    } catch (Exception $e) {
        error_log("Error updating note: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error updating note']);
    }
    exit;
}

// If no specific action is requested, return an error
if (!isset($_GET['get_notes'])) {
    echo json_encode(['success' => false, 'message' => 'No valid action specified']);
}
?>