<?php
include 'db_connect.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do cliente não fornecido.");
}

$cliente_id = intval($_GET['id']);

// Buscar informações do cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cliente) {
    die("Cliente não encontrado.");
}

// Buscar todos os exames disponíveis
$exames_disponiveis = $conn->query("SELECT id, nome FROM exames ORDER BY nome ASC")->fetch_all(MYSQLI_ASSOC);

// Associar exame ao cliente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['exame_id'])) {
    $exame_id = $_POST['exame_id'];
    $stmt = $conn->prepare("INSERT INTO cliente_exames (cliente_id, exame_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $cliente_id, $exame_id);
    if ($stmt->execute()) {
        echo "<script>alert('Exame associado com sucesso!'); window.location.href='detalhes_cliente.php?id=$cliente_id';</script>";
    } else {
        echo "Erro ao associar exame.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associar Exame</title>
    <link rel="stylesheet" href="css/adicionar-exame.css">
</head>
<body>
    <div class="container">
        <h2>Associar Exame a <?php echo htmlspecialchars($cliente['nome']); ?></h2>
        
        <form method="POST">
            <label for="exame_id">Escolha um Exame:</label>
            <select name="exame_id" id="exame_id" required>
                <option value="">-- Escolha um Exame --</option>
                <?php foreach ($exames_disponiveis as $exame) {
                    echo "<option value='" . $exame['id'] . "'>" . htmlspecialchars($exame['nome']) . "</option>";
                } ?>
            </select>
            <button type="submit">Associar Exame</button>
        </form>
        
        <a href="detalhes_cliente.php?id=<?php echo $cliente_id; ?>" class="back-link">Voltar</a>
    </div>
</body>
</html>
