<?php
include 'db_connect.php';

$mensagem = '';
$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $pin = $_POST['pin'] ?? '';

    if (!$nome || !$email || !$senha) {
        $erro = "Preencha todos os campos obrigatórios.";
    } else {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        if ($pin === '3301') {
            // Criar conta admin
            $stmt = $conn->prepare("INSERT INTO admins (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $senhaHash);
            if ($stmt->execute()) {
                $mensagem = "Conta de administrador criada com sucesso!";
            } else {
                $erro = "Erro ao criar conta de administrador.";
            }
        } else {
            // Criar conta usuário
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $senhaHash);
            if ($stmt->execute()) {
                $mensagem = "Conta de usuário criada com sucesso!";
            } else {
                $erro = "Erro ao criar conta de usuário.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Conta</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Criar Conta</h2>

        <?php if ($mensagem): ?>
            <div style="color: green; text-align:center; margin-bottom: 15px;"><?php echo $mensagem; ?></div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="erro"><?php echo $erro; ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" required>

            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="senha">Senha:</label>
            <input type="password" name="senha" required>

            <label for="pin">PIN (apenas para admins):</label>
            <input type="text" name="pin" placeholder="Deixe em branco para usuário comum">

            <button type="submit">Criar Conta</button>
        </form>

        <a class="link-criar-conta" href="login.php">Voltar para o Login</a>
    </div>
</body>
</html>
