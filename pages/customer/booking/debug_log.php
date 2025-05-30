<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Logs</title>
    <style>
        body { font-family: monospace; margin: 20px; }
        .log-entry { 
            padding: 5px; 
            margin: 2px 0; 
            background: #f5f5f5; 
            border-left: 3px solid #007cba;
        }
        .clear-btn {
            background: #ff4444;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h2>Debug Logs</h2>
    
    <form method="POST">
        <button type="submit" name="clear_logs" class="clear-btn">Clear Logs</button>
    </form>
    
    <?php
    if (isset($_POST['clear_logs'])) {
        unset($_SESSION['debug_logs']);
        echo "<p>Logs cleared!</p>";
    }
    
    if (isset($_SESSION['debug_logs']) && !empty($_SESSION['debug_logs'])) {
        echo "<div>";
        foreach ($_SESSION['debug_logs'] as $log) {
            echo "<div class='log-entry'>" . htmlspecialchars($log) . "</div>";
        }
        echo "</div>";
        
        // Add JavaScript to show logs in console too
        echo "<script>";
        foreach ($_SESSION['debug_logs'] as $log) {
            echo "console.log(" . json_encode($log) . ");";
        }
        echo "</script>";
    } else {
        echo "<p>No debug logs available.</p>";
    }
    ?>
    
    <br><br>
    <a href="/NEW-PM-JI-RESERVIFY/pages/customer/booking/">← Back to Booking</a>
</body>
</html>