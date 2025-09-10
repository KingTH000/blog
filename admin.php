<?php
session_start();
require 'casbin.php';

// Optional: only allow admin users
// if ($_SESSION['role'] !== 'admin') { die("Access denied"); }

$message = "";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_role'])) {
        $result = casbinAddRoleForUser($_POST['user'], $_POST['role']);
        $message = "Role added: " . json_encode($result);
    } elseif (isset($_POST['delete_role'])) {
        $result = casbinDeleteRoleForUser($_POST['user'], $_POST['role']);
        $message = "Role deleted: " . json_encode($result);
    } elseif (isset($_POST['add_policy'])) {
        $result = casbinAddPolicy($_POST['sub'], $_POST['obj'], $_POST['act']);
        $message = "Policy added: " . json_encode($result);
    } elseif (isset($_POST['delete_policy'])) {
        $result = casbinDeletePolicy($_POST['sub'], $_POST['obj'], $_POST['act']);
        $message = "Policy deleted: " . json_encode($result);
    }
}

// Fetch existing roles and policies
$roles = casbinGetAllRoles();
$policies = casbinGetAllPolicies();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Casbin Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
        input[type=text] { width: 200px; }
        input[type=submit] { margin-top: 5px; }
        h2 { margin-top: 40px; }
        .msg { color: green; font-weight: bold; }
        table { border-collapse: collapse; margin-top: 15px; width: 90%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Casbin Admin Panel</h1>
    <?php if ($message): ?>
        <p class="msg"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Role Management -->
    <h2>Manage Roles</h2>
    <form method="post">
        <label>User: <input type="text" name="user" required></label><br>
        <label>Role: <input type="text" name="role" required></label><br>
        <input type="submit" name="add_role" value="Add Role to User">
    </form>

    <?php
    //var_dump($roles);
    //var_dump($policies);
    ?>

    <h3>Existing Roles</h3>
    <?php if (!empty($roles)): ?>
        <table>
            <tr><th>User</th><th>Role</th><th>Action</th></tr>
            <?php foreach ($roles['roles'] as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['user']); ?></td>
                    <td><?php echo htmlspecialchars($r['role']); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="user" value="<?php echo htmlspecialchars($r['user']); ?>">
                            <input type="hidden" name="role" value="<?php echo htmlspecialchars($r['role']); ?>">
                            <input type="submit" name="delete_role" value="Delete" onclick="return confirm('Delete this role?');">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No roles found.</p>
    <?php endif; ?>

    <!-- Policy Management -->
    <h2>Manage Policies</h2>
    <form method="post">
        <label>Subject (user/role): <input type="text" name="sub" required></label><br>
        <label>Object (resource): <input type="text" name="obj" required></label><br>
        <label>Action: <input type="text" name="act" required></label><br>
        <input type="submit" name="add_policy" value="Add Policy">
    </form>

    <h3>Existing Policies</h3>
    <?php if (!empty($policies)): ?>
        <table>
            <tr><th>Subject</th><th>Object</th><th>Action</th><th>Action</th></tr>
            <?php foreach ($policies['policies'] as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['sub']); ?></td>
                    <td><?php echo htmlspecialchars($p['obj']); ?></td>
                    <td><?php echo htmlspecialchars($p['act']); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="sub" value="<?php echo htmlspecialchars($p[0]); ?>">
                            <input type="hidden" name="obj" value="<?php echo htmlspecialchars($p[1]); ?>">
                            <input type="hidden" name="act" value="<?php echo htmlspecialchars($p[2]); ?>">
                            <input type="submit" name="delete_policy" value="Delete" onclick="return confirm('Delete this policy?');">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No policies found.</p>
    <?php endif; ?>
</body>
</html>
