<?php
function validateSessionRole($required_roles) {
    
    // Split the required roles into an array
    $roles_array = explode(', ', $required_roles);

    if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], $roles_array)) {
        return true;  
    } else {
        header("Location: ../");
        exit();
    }
}
?>
