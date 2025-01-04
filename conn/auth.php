<?php
function validateSessionRole($required_roles)
{
    if (!is_array($required_roles)) {
        $roles_array = explode(',', $required_roles);
    } else {
        $roles_array = $required_roles;
    }

    $roles_array = array_map('trim', $roles_array);

    if (isset($_SESSION['user_role'])) {
        if ($_SESSION['user_role'] === 'super_admin' || in_array($_SESSION['user_role'], $roles_array)) {
            return true;
        }
    }

    header("Location: ../");
    exit();
}

?>