<?php
function validateSessionRole($required_roles) {
    
    $roles_array = explode(', ', $required_roles);

    if (isset($_SESSION['user_role'])) {
        // Allow access if the user is 'super_admin' or the role is in the required roles
        if ($_SESSION['user_role'] === 'super_admin' || in_array($_SESSION['user_role'], $roles_array)) {
            return true;
        }
    }

    header("Location: ../");
    exit();
}
?>
