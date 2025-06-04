<?php
session_start();

session_unset();

session_destroy();

header("Location: /NEW-PM-JI-RESERVIFY/pages/admin/index.php");
exit();
?>