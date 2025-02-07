<?php
// Includi il file di configurazione
include('config.php');
session_start();

// Verifica se il modulo è stato inviato
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = strtolower(trim($_POST['username'])); // Trasforma in minuscolo e rimuove spazi
    $password = $_POST['password'];

    // Validazioni lato server
    if (strlen($username) < 4 || strlen($password) < 4) {
        $error_message = "L'username e la password devono essere di almeno 4 caratteri.";
    } elseif (!ctype_lower($username)) {
        $error_message = "L'username deve essere tutto in lettere minuscole.";
    } else {
        // Controlla che l'username non esista già in `pending_users` o `users`
        $sql_check = "SELECT id FROM pending_users WHERE username = ? UNION SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql_check);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "L'username è già in uso. Scegli un altro username.";
        } else {
            // Inserisci l'utente nella tabella `pending_users`
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO pending_users (username, password_hash, created_at) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username, $password_hash);

            if ($stmt->execute()) {
                $success_message = "Registrazione completata. Per accedere l'account deve essere approvato da un amministratore.";
                //header("Location: login.php");
            } else {
                $error_message = "Errore durante la registrazione. Riprova più tardi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Registrazione</title>
</head>

<body>
    <h1>Registrazione</h1>

    <?php if (isset($error_message)) echo "<p style='color: red;'>$error_message</p>"; ?>
    <?php if (isset($success_message)) echo "<p style='color: green;'>$success_message</p>"; ?>

    <form method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <button type="submit">Registrati</button>
    </form>
    <p>Hai già un account? <a href="login.php">Accedi qui</a>.</p>
</body>

</html>