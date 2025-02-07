<?php
// Includi il file di configurazione
include('config.php');

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim(strtolower($_POST['username']));
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = "Tutti i campi sono obbligatori!";
    } else {
        // Controlla credenziali nella tabella users
        $sql = "SELECT id, password_hash, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Username o password errati.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>

<body>
    <h1>Login</h1>
    <?php if (isset($error_message)) echo "<p style='color: red;'>$error_message</p>"; ?>
    <form action="login.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>
        <button type="submit">Accedi</button>
    </form>
    <p>Non hai un account? <a href="register.php">Registrati</a></p>
</body>

</html>