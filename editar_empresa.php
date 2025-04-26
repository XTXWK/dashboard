<?php
include 'db_connect.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('Empresa não encontrada.'); window.location.href='lista_empresas.php';</script>";
    exit;
}

$id = intval($_GET['id']);

// Buscar dados da empresa
$sql = "SELECT * FROM empresas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Empresa não encontrada.'); window.location.href='lista_empresas.php';</script>";
    exit;
}

$empresa = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST['nome'];
    $cnpj = $_POST['cnpj'];

    $sql = "UPDATE empresas SET nome = ?, cnpj = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $nome, $cnpj, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Empresa atualizada com sucesso.'); window.location.href='lista_empresas.php';</script>";
    } else {
        echo "<script>alert('Erro ao atualizar empresa.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Empresa</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            padding: 40px;
        }

        .container {
            background: #fff;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        button {
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            width: 100%;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #007bff;
            font-weight: bold;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Editar Empresa</h2>
        <form method="POST">
            <label for="nome">Nome da Empresa:</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($empresa['nome']) ?>" required>

            <label for="cnpj">CNPJ:</label>
            <input type="text" id="cnpj" name="cnpj" value="<?= htmlspecialchars($empresa['cnpj']) ?>" required>

            <button type="submit">Salvar Alterações</button>
        </form>
        <a href="lista_empresas.php" class="back-link">← Voltar à Lista</a>
    </div>
</body>
</html>
