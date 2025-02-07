<?php
// Includi il file di configurazione
include('config.php');
session_start();

// Controlla se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Recupera i dettagli dell'utente loggato
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Controlla se l'utente è stato trovato
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Cambia la password se richiesto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'];
    $password_hash = password_hash($new_password, PASSWORD_BCRYPT);

    $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $password_hash, $user_id);
    if ($stmt->execute()) {
        $success_message = "Password aggiornata con successo.";
    } else {
        $error_message = "Errore durante l'aggiornamento della password.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>

<body>
    <h1>Benvenuto, <?= htmlspecialchars($user['username']) ?>!</h1>

    <?php if (isset($success_message)) echo "<p style='color: green;'>$success_message</p>"; ?>
    <?php if (isset($error_message)) echo "<p style='color: red;'>$error_message</p>"; ?>

    <h2>Cambia la tua password</h2>
    <form method="POST">
        <label for="new_password">Nuova Password:</label>
        <input type="password" name="new_password" id="new_password" required><br>
        <button type="submit">Aggiorna Password</button>
    </form>

    <?php if ($user['role'] === 'admin'): ?>
        <h2>Admin Panel</h2>
        <p><a href="admin.php">Vai al pannello di amministrazione</a></p>
    <?php endif; ?>

    <p><a href="logout.php">Logout</a></p>
</body>

</html>