<?php
include 'db_connect.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do exame não fornecido.");
}

$exame_id = intval($_GET['id']);

// Buscar exame
$stmt = $conn->prepare("SELECT * FROM exames WHERE id = ?");
$stmt->bind_param("i", $exame_id);
$stmt->execute();
$exame = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exame) {
    die("Exame não encontrado.");
}

// Atualizar exame
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_nome = $_POST['nome'];
    $descricao = $_POST['descricao'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    
    $stmt = $conn->prepare("UPDATE exames SET nome = ?, descricao = ?, categoria = ? WHERE id = ?");
    $stmt->bind_param("sssi", $novo_nome, $descricao, $categoria, $exame_id);
    if ($stmt->execute()) {
        header("Location: lista_exames.php");
        exit();
    } else {
        echo "<script>alert('Erro ao atualizar exame.');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Exame</title>
    <link rel="stylesheet" href="css/editar-exame.css">
    
</head>
<body>
    <div class="container">
        <h2>Editar Exame</h2>
        <form method="POST" action="">
            <label for="nome">Nome do Exame:</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($exame['nome']); ?>" required>
            
            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao"><?php echo htmlspecialchars($exame['descricao'] ?? ''); ?></textarea>
            
            <label for="categoria">Categoria:</label>
            <input type="text" id="categoria" name="categoria" value="<?php echo htmlspecialchars($exame['categoria'] ?? ''); ?>">
            
            <button type="submit">Salvar Alterações</button>
        </form>
        <a href="lista_exames.php" class="back-link">Voltar</a>
    </div>
</body>
</html>
