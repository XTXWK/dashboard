<?php
include 'db_connect.php';

if (!isset($_GET['id'])) {
    echo "ID do cliente não fornecido!";
    exit;
}

$id = $_GET['id'];

// Buscar os dados atuais do cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();

if (!$cliente) {
    echo "Cliente não encontrado!";
    exit;
}

// Atualizar os dados se o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $empresa = $_POST['empresa'];
    $cargo = $_POST['cargo'];

    $update = $conn->prepare("UPDATE clientes SET nome = ?, cpf = ?, empresa = ?, cargo = ? WHERE id = ?");
    $update->bind_param("ssssi", $nome, $cpf, $empresa, $cargo, $id);

    if ($update->execute()) {
        echo "<script>alert('Cliente atualizado com sucesso!'); window.location.href='detalhes_cliente.php?id=$id';</script>";
        exit;
    } else {
        echo "Erro ao atualizar o cliente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/editar_cliente.css">
    
</head>
<body>

<div class="form-container">
    <h2><i class="fa fa-edit"></i> Editar Cliente</h2>
    <form method="post">
        <label for="nome">Nome</label>
        <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($cliente['nome']); ?>" required>

        <label for="cpf">CPF</label>
        <input type="text" name="cpf" id="cpf" value="<?php echo htmlspecialchars($cliente['cpf']); ?>" required>

        <label for="empresa">Empresa</label>
        <input type="text" name="empresa" id="empresa" value="<?php echo htmlspecialchars($cliente['empresa']); ?>">

        <label for="cargo">Cargo</label>
        <input type="text" name="cargo" id="cargo" value="<?php echo htmlspecialchars($cliente['cargo']); ?>">

        <button type="submit"><i class="fa fa-save"></i> Salvar Alterações</button>
    </form>
    <a class="back-link" href="detalhes_cliente.php?id=<?php echo $cliente['id']; ?>"><i class="fa fa-arrow-left"></i> Voltar</a>
</div>

</body>
</html>
