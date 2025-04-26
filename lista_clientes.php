<?php
include 'db_connect.php';
session_start(); // Não esqueça de iniciar a sessão!

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$tipoUsuario = $_SESSION['tipo']; // Pode ser 'admin' ou 'dev'
$dashboardLink = ($tipoUsuario === 'admin') ? 'index.php' : 'dashboard_dev.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$query = "SELECT id, nome, cpf, empresa, cargo, idade FROM clientes";
if (!empty($search)) {
    $query .= " WHERE nome LIKE ? ORDER BY nome ASC";
    $stmt = $conn->prepare($query);
    $searchParam = "%" . $search . "%";
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $clientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $clientes = $conn->query($query . " ORDER BY nome ASC")->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Clientes</title>
    <link rel="stylesheet" href="css/lista_clientes.css">
</head>
<body>
    <div class="container">
        <h2>Lista de Clientes</h2>
        <div class="search-box">
            <form method="GET">
                <input type="text" name="search" placeholder="Pesquisar cliente pelo nome" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Empresa</th>
                    <th>Cargo</th>
                    <th>Idade</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente) : ?>
                    <tr>
                        <td><?php echo $cliente['id']; ?></td>
                        <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['cpf']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['empresa']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['cargo']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['idade']); ?></td>
                        <td>
                            <a href="detalhes_cliente.php?id=<?php echo $cliente['id']; ?>" style="color: #007bff; text-decoration: none;">Ver Detalhes</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Link de voltar ao dashboard -->
        <a href="<?php echo $dashboardLink; ?>" class="back-link">Voltar ao Dashboard</a>
    </div>
</body>
</html>
