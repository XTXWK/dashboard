<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['nome'])) {
        $nome = $_POST['nome'];
        $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : "";
        $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : "";
        $preparacao = isset($_POST['preparacao']) ? $_POST['preparacao'] : "";
        $duracao = isset($_POST['duracao']) ? $_POST['duracao'] : "";
        
        if (!empty($nome)) {
            $stmt = $conn->prepare("INSERT INTO exames (nome, descricao, tipo, preparacao, duracao) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nome, $descricao, $tipo, $preparacao, $duracao);
            if ($stmt->execute()) {
                echo "<script>alert('Exame cadastrado com sucesso!'); window.location.href='cadastro_exame.php';</script>";
            } else {
                echo "Erro ao cadastrar exame.";
            }
            $stmt->close();
        } else {
            echo "<script>alert('O nome do exame é obrigatório!');</script>";
        }
    } elseif (isset($_POST['exame_id'])) {
        $exame_id = intval($_POST['exame_id']);
        $stmt = $conn->prepare("DELETE FROM exames WHERE id = ?");
        $stmt->bind_param("i", $exame_id);
        if ($stmt->execute()) {
            echo "<script>alert('Exame excluído com sucesso!'); window.location.href='cadastro_exame.php';</script>";
        } else {
            echo "Erro ao excluir exame.";
        }
        $stmt->close();
    }
}

$exames = $conn->query("SELECT id, nome FROM exames ORDER BY nome ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Exame</title>
    <link rel="stylesheet" href="css/cadastro_exame.css">
</head>
<body>
    <div class="container">
        <h2>Cadastro de Exame</h2>
        <form method="POST" action="">
            <label for="nome">Nome do Exame:</label>
            <input type="text" name="nome" id="nome" required>
            
            <label for="descricao">Descrição (Opcional):</label>
            <textarea name="descricao" id="descricao"></textarea>
            
            <label for="tipo">Tipo de Exame (Opcional):</label>
            <select name="tipo" id="tipo">
                <option value="">-- Selecione --</option>
                <option value="Laboratorial">Laboratorial</option>
                <option value="Imagem">Imagem</option>
                <option value="Outros">Outros</option>
            </select>
            
            <label for="preparacao">Preparação Necessária (Opcional):</label>
            <textarea name="preparacao" id="preparacao"></textarea>
            
            <label for="duracao">Duração do Exame (Opcional):</label>
            <input type="text" name="duracao" id="duracao">
            
            <button type="submit">Cadastrar</button>
        </form>

        <a href="lista_exames.php" class="back-link btn-lista">Lista de Exames</a>
        <a href="index.php" class="back-link btn-back">Voltar ao Dashboard</a>
    </div>
</body>
</html>
