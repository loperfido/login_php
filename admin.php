<?php
// Includi il file di configurazione
include('config.php');
session_start();

// Controlla se l'utente è loggato e se è un amministratore
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Gestione delle azioni dell'amministratore
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve'])) {
        $id = $_POST['approve'];
        $sql = "INSERT INTO users (username, password_hash, created_at, role)
                SELECT username, password_hash, created_at, 'user' FROM pending_users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $sql = "DELETE FROM pending_users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif (isset($_POST['delete_user'])) {
        $id = $_POST['delete_user'];
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif (isset($_POST['delete_pending'])) {
        $id = $_POST['delete_pending'];
        $sql = "DELETE FROM pending_users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif (isset($_POST['update_password'])) {
        $id = $_POST['update_password'];
        $new_password = $_POST['new_password'];
        $password_hash = password_hash($new_password, PASSWORD_BCRYPT);

        $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $password_hash, $id);
        $stmt->execute();
    } elseif (isset($_POST['update_role'])) {
        $id = $_POST['update_role'];
        $new_role = $_POST['new_role'];

        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_role, $id);
        $stmt->execute();
    }
}

// Recupera gli utenti in attesa di approvazione
$pending_users = $conn->query("SELECT * FROM pending_users")->fetch_all(MYSQLI_ASSOC);

// Recupera gli utenti registrati
$approved_users = $conn->query("SELECT * FROM users")->fetch_all(MYSQLI_ASSOC);

// Query per ottenere il numero di utenti per data
$sql_chart = "SELECT DATE(created_at) AS registration_date, COUNT(*) AS user_count
              FROM users
              GROUP BY DATE(created_at)
              ORDER BY registration_date";
$result_chart = $conn->query($sql_chart);

$dates = [];
$user_counts = [];

while ($row = $result_chart->fetch_assoc()) {
    $dates[] = $row['registration_date'];
    $user_counts[] = $row['user_count'];
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Pannello Amministrativo</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        form {
            display: inline;
        }
    </style>
</head>

<body>
    <h1>Pannello Amministrativo</h1>

    <!-- Grafico Utenti -->
    <h2>Utenti Registrati nel Tempo</h2>
    <canvas id="userChart" width="800" height="400"></canvas>
    <script>
        const ctx = document.getElementById('userChart').getContext('2d');
        const userChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($dates) ?>, // Date estratte dal database
                datasets: [{
                    label: 'Numero di Utenti',
                    data: <?= json_encode($user_counts) ?>, // Contatore utenti
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    },
                    title: {
                        display: true,
                        text: 'Utenti Registrati nel Tempo'
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Data di Registrazione'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Numero di Utenti'
                        }
                    }
                }
            }
        });
    </script>

    <!-- Tabella Utenti in Attesa -->
    <h2>Utenti in Attesa di Approvazione</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Data Registrazione</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pending_users)): ?>
                <tr>
                    <td colspan="4">Nessun utente in attesa.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pending_users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                        <td>
                            <form method="POST">
                                <button type="submit" name="approve" value="<?= $user['id'] ?>">Approva</button>
                            </form>
                            <form method="POST">
                                <button type="submit" name="delete_pending" value="<?= $user['id'] ?>">Elimina</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Tabella Utenti Registrati -->
    <h2>Utenti Registrati</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Ruolo</th>
                <th>Data Registrazione</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($approved_users)): ?>
                <tr>
                    <td colspan="5">Nessun utente registrato.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($approved_users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                        <td>
                            <form method="POST">
                                <input type="text" name="new_password" placeholder="Nuova Password" required>
                                <button type="submit" name="update_password" value="<?= $user['id'] ?>">Aggiorna Password</button>
                            </form>
                            <form method="POST">
                                <select name="new_role" required>
                                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <button type="submit" name="update_role" value="<?= $user['id'] ?>">Aggiorna Ruolo</button>
                            </form>
                            <form method="POST">
                                <button type="submit" name="delete_user" value="<?= $user['id'] ?>">Elimina</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p><a href="dashboard.php">Torna alla Dashboard</a></p>
</body>

</html>