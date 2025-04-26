<?php
include 'db_connect.php';

if (!isset($_GET['id'])) {
    echo "ID do administrador não especificado.";
    exit;
}

$id = intval($_GET['id']);
$mensagem = "";

// Buscar dados atuais do admin
$stmt = $conn->prepare("SELECT nome, email FROM admins WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    echo "Administrador não encontrado.";
    exit;
}

// Atualizar dados
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novoNome = trim($_POST["nome"]);
    $novoEmail = trim($_POST["email"]);
    $novaSenha = trim($_POST["senha"]);

    if (!empty($novaSenha)) {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET nome = ?, email = ?, senha = ? WHERE id = ?");
        $stmt->bind_param("sssi", $novoNome, $novoEmail, $senhaHash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE admins SET nome = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $novoNome, $novoEmail, $id);
    }

    if ($stmt->execute()) {
        $mensagem = "Administrador atualizado com sucesso.";
        $admin['nome'] = $novoNome;
        $admin['email'] = $novoEmail;
    } else {
        $mensagem = "Erro ao atualizar.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Administrador</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f4f7;
            padding: 30px;
        }

        .container {
            max-width: 500px;
            background: white;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .mensagem {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
            color: #155724;
        }

        .voltar {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #007bff;
            text-decoration: none;
        }

        .voltar:hover {
            text-decoration: underline;
        }

        small {
            color: #666;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Editar Administrador</h2>

    <?php if ($mensagem): ?>
        <div class="mensagem"><?= $mensagem ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($admin['nome']) ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>

        <label for="senha">Nova Senha:</label>
        <input type="password" name="senha" placeholder="Deixe em branco para manter a senha atual">
        <small>A senha só será alterada se você preencher este campo.</small>

        <button type="submit">Salvar Alterações</button>
    </form>

    <a href="lista_admin.php" class="voltar">← Voltar para Lista</a>
</div>
</body>
</html>
