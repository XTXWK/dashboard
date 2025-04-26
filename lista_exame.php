<?php
include 'db_connect.php';

// Verifica se o usuário está logado e qual é o tipo de usuário
session_start(); // Inicia a sessão para acessar as variáveis de sessão
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null; // Supondo que 'user_type' armazene o tipo de usuário

// Exclusão de exame via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['exame_id'])) {
    $exame_id = intval($_POST['exame_id']);
    $stmt = $conn->prepare("DELETE FROM exames WHERE id = ?");
    $stmt->bind_param("i", $exame_id);
    if ($stmt->execute()) {
        echo "<script>alert('Exame excluído com sucesso!'); window.location.href='lista_exames.php';</script>";
    } else {
        echo "<script>alert('Erro ao excluir exame!');</script>";
    }
    $stmt->close();
}

// Buscar exames
$exames = $conn->query("SELECT id, nome FROM exames ORDER BY nome ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Exames</title>
    <link rel="stylesheet" href="css/lista_exame.css">
    <style>
        /* Seu estilo CSS */
    </style>
</head>
<body>
    <div class="container">
        <h2>Lista de Exames</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome do Exame</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exames as $exame): ?>
                <tr>
                    <td data-label="ID"><?php echo $exame['id']; ?></td>
                    <td data-label="Nome do Exame"><?php echo htmlspecialchars($exame['nome']); ?></td>
                    <td data-label="Ações">
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="exame_id" value="<?php echo $exame['id']; ?>">
                            <a href="editar-exame.php?id=<?php echo $exame['id']; ?>" class="edit-btn-2">Editar</a>
                            <button type="submit" class="delete-btn-2" onclick="return confirm('Tem certeza que deseja excluir este exame?')">Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        // Determina o link de "voltar" com base no tipo de usuário
        if ($user_type == 'dev') {
            $dashboard_url = 'dashboard_dev.php';
        } else {
            $dashboard_url = 'index.php';
        }
        ?>

        <a href="<?php echo $dashboard_url; ?>" class="back-link">Voltar ao Dashboard</a>
    </div>
</body>
</html>
