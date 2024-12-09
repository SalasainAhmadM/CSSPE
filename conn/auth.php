<?php
function validateSessionRole($required_role) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === $required_role) {
        return true;  
    } else {
       
        header("Location: ../");
        exit();
    }
}
?>