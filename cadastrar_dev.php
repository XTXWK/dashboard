<?php
session_start();
include 'db_connect.php';

// Verifica se o usuário está logado como admin
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");  // Redireciona para a página de login
    exit;  // Interrompe a execução do script
}

$erro_nome = '';
$erro_email = '';
$erro_senha = '';
$erro_nivel = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $nivel = $_POST['nivel'] ?? '';

    // Validação dos campos
    if (empty($nome)) {
        $erro_nome = "O nome é obrigatório.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro_email = "O email é inválido.";
    }

    if (empty($senha)) {
        $erro_senha = "A senha é obrigatória.";
    } elseif (strlen($senha) < 6) {
        $erro_senha = "A senha deve ter pelo menos 6 caracteres.";
    }

    if (empty($nivel) || !in_array($nivel, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10])) {
        $erro_nivel = "O nível de acesso é inválido.";
    }

    // Se não houver erros, tenta cadastrar o dev
    if (empty($erro_nome) && empty($erro_email) && empty($erro_senha) && empty($erro_nivel)) {
        // Verificar se o email já está cadastrado
        $stmt = $conn->prepare("SELECT id FROM devs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $erro_email = "O email já está cadastrado.";
        } else {
            // Criptografando a senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            // Inserir o novo dev no banco de dados
            $stmt = $conn->prepare("INSERT INTO devs (nome, email, senha, nivel) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $nome, $email, $senha_hash, $nivel);

            if ($stmt->execute()) {
                $sucesso = "Desenvolvedor cadastrado com sucesso!";
            } else {
                $erro_email = "Erro ao cadastrar desenvolvedor. Tente novamente.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Desenvolvedor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", sans-serif;
            background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-box {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 10px;
            margin-bottom: 4px;
            color: #333;
            font-weight: 500;
        }

        input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            transition: border 0.3s ease;
        }

        input:focus {
            border-color: #1a73e8;
            box-shadow: 0 0 5px rgba(26, 115, 232, 0.2);
        }

        button {
            margin-top: 20px;
            padding: 12px;
            background-color: #1a73e8;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #1558c0;
        }

        .erro {
            background-color: #ffe1e1;
            color: #d8000c;
            border: 1px solid #d8000c;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            font-size: 14px;
        }

        .sucesso {
            background-color: #e1f7d5;
            color: #4bbd60;
            border: 1px solid #4bbd60;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Cadastrar Desenvolvedor</h2>

        <?php if ($sucesso): ?>
            <div class="sucesso"><?= $sucesso ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Nome:</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($nome ?? '') ?>" required>
            <?php if ($erro_nome): ?><div class="erro"><?= $erro_nome ?></div><?php endif; ?>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
            <?php if ($erro_email): ?><div class="erro"><?= $erro_email ?></div><?php endif; ?>

            <label>Senha:</label>
            <input type="password" name="senha" required>
            <?php if ($erro_senha): ?><div class="erro"><?= $erro_senha ?></div><?php endif; ?>

            <label>Nível de Acesso:</label>
            <input type="number" name="nivel" value="<?= htmlspecialchars($nivel ?? '') ?>" min="1" max="10" required>
            <?php if ($erro_nivel): ?><div class="erro"><?= $erro_nivel ?></div><?php endif; ?>

            <button type="submit">Cadastrar</button>
            <a href="index.php"><button">voltar ao Dashboard</button></a>
        </form>
    </div>
</body>
</html>
