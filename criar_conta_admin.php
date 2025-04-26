<?php
include 'db_connect.php';

$mensagem = '';
$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $pin   = trim($_POST['pin'] ?? '');

    if (!$nome || !$email || !$senha || !$pin) {
        $erro = "Preencha todos os campos obrigatórios.";
    } else {
        if ($pin !== '3301') {
            $erro = "PIN incorreto. Conta de administrador não criada.";
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $senhaHash);
            
            if ($stmt->execute()) {
                $mensagem = "Conta de administrador criada com sucesso!";
            } else {
                $erro = "Erro ao criar conta de administrador: " . $conn->error;
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
    <title>Criar Conta Administrador</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Reaproveitando o CSS do login para consistência visual -->
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Criar Conta Administrador</h2>

        <?php if ($mensagem): ?>
            <div class="mensagem sucesso"><?php echo $mensagem; ?></div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="mensagem erro"><?php echo $erro; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" required>

            <label for="email">Email (será usado como login):</label>
            <input type="email" name="email" id="email" required>

            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" required>

            <label for="pin">PIN de Administrador:</label>
            <input type="text" name="pin" id="pin" required>

            <button type="submit">Criar Conta</button>
        </form>

        <a class="link-criar-conta" href="login.php">Voltar para o Login</a>
    </div>
</body>
</html>
