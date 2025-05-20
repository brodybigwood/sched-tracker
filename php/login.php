<?php
session_start();

$dbfile = '../weeks.db';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            $db = new SQLite3($dbfile);
            $statement = $db->prepare("SELECT employee_id, name, password_hash FROM Employees WHERE name = :username");
            $statement->bindValue(':username', $username);
            $result = $statement->execute();
            $user = $result->fetchArray(SQLITE3_ASSOC);
            $db->close();

            if (!$user) {
                $error = "Invalid username";

            } elseif (password_verify($password, $user['password_hash'])) {
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['employee_id'];
                $_SESSION['username'] = $user['name'];

                header("Location: app.php"); 
                exit;
            } else {
                $error = "Invalid password.";
            }

        } catch (SQLite3Exception $e) {
        $error = "Database error: " . $e->getMessage();
        }
    }
    
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

    <h1 id='form-header'>Login</h1>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="post" action="login.php">
    <div>
        <label for="username">Username:</label>
        <input type='text' id='username' name='username' required>
    </div>
    <div>
        <label for="password">Password:</label>
        <input type='password' id='password' name='password' required>
    </div>
    <button id='submit' type='submit'>Login</button>
    <p>Logging in for the first time? Contact the admin to recieve login information</p>
</form>

</body>
</html>