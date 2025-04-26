<?php
session_start();
include 'db_connect.php';

$erro_usuario = '';
$erro_admin = '';

// Login de cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo']) && $_POST['tipo'] === 'cliente') {
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $stmt = $conn->prepare("SELECT id, cliente_id, login, senha FROM cliente_contas WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $cliente = $result->fetch_assoc();
        if (password_verify($senha, $cliente['senha'])) {
            $_SESSION['usuario_id'] = $cliente['id'];
            $_SESSION['cliente_id'] = $cliente['cliente_id'];
            $_SESSION['usuario'] = $cliente['login'];
            $_SESSION['tipo'] = 'cliente';
            header("Location: detalhes_cliente.php?id=" . $cliente['cliente_id']);
            exit;
        } else {
            $erro_usuario = "Senha incorreta.";
        }
    } else {
        $erro_usuario = "Usuário não encontrado.";
    }
    $stmt->close();
}

// Login de admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo']) && $_POST['tipo'] === 'admin') {
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $pin = $_POST['pin'] ?? '';

    $stmt = $conn->prepare("SELECT id, nome, email, senha FROM admins WHERE email = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        if ($pin !== '3301') {
            $erro_admin = "PIN de administrador incorreto.";
        } else {
            $admin = $result->fetch_assoc();
            if (password_verify($senha, $admin['senha'])) {
                $_SESSION['usuario_id'] = $admin['id'];
                $_SESSION['usuario'] = $admin['nome'];
                $_SESSION['tipo'] = 'admin';
                header("Location: index.php");
                exit;
            } else {
                $erro_admin = "Senha incorreta.";
            }
        }
    } else {
        $erro_admin = "Administrador não encontrado.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema</title>
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

    .login-box {
        background-color: #ffffff;
        padding: 40px 30px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 420px;
        transition: all 0.3s ease;
    }

    .tabs {
        display: flex;
        justify-content: space-around;
        margin-bottom: 25px;
        border-bottom: 2px solid #e0e0e0;
    }

    .tab {
        cursor: pointer;
        padding: 10px;
        font-weight: 600;
        flex: 1;
        text-align: center;
        color: #555;
        border-bottom: 3px solid transparent;
        transition: 0.2s;
    }

    .tab.active {
        color: #1a73e8;
        border-color: #1a73e8;
        background-color: #f5faff;
        border-radius: 8px 8px 0 0;
    }

    .form-section {
        display: none;
    }

    .form-section.active {
        display: block;
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

    a {
        color: #1a73e8;
        text-decoration: none;
        font-weight: 500;
    }

    a:hover {
        text-decoration: underline;
    }

    p {
        margin-top: 15px;
        font-size: 14px;
        text-align: center;
    }
</style>

</head>
<body>
    <div class="login-box">
        <div class="tabs">
            <div class="tab active" onclick="showTab('usuario')">Usuário</div>
            <div class="tab" onclick="showTab('admin')">Administrador</div>
        </div>

        <!-- Formulário Usuário -->
        <div id="usuario" class="form-section active">
            <?php if ($erro_usuario): ?><div class="erro"><?= $erro_usuario ?></div><?php endif; ?>
            <form method="POST">
                <input type="hidden" name="tipo" value="cliente">
                <label>CPF:</label>
                <input type="text" name="login" placeholder="Digite seu CPF" required>
                <label>Senha:</label>
                <input type="password" name="senha" required>
                <button type="submit">Entrar</button>
            </form>
        </div>

        <!-- Formulário Admin -->
        <div id="admin" class="form-section">
            <?php if ($erro_admin): ?><div class="erro"><?= $erro_admin ?></div><?php endif; ?>
            <form method="POST">
                <input type="hidden" name="tipo" value="admin">
                <label>Email:</label>
                <input type="email" name="login" placeholder="Digite seu email" required>
                <label>Senha:</label>
                <input type="password" name="senha" required>
                <label>PIN de Administrador:</label>
                <input type="text" name="pin" required>
                <button type="submit">Entrar como Admin</button>
            </form>
            <p style="margin-top: 10px;">Não tem uma conta? <a href="criar_conta_admin.php">Criar Conta Admin</a></p>
        </div>
    </div>

    <script>
        function showTab(tipo) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.form-section').forEach(section => section.classList.remove('active'));
            document.querySelector('.tab[onclick="showTab(\'' + tipo + '\')"]').classList.add('active');
            document.getElementById(tipo).classList.add('active');
        }
    </script>
</body>
</html>
