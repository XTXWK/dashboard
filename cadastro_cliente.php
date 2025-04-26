<?php
include 'db_connect.php';
session_start();

// Verificação de acesso mais robusta
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Verifica se é admin OU dev com nível >= 5
$isAdmin = ($_SESSION['tipo'] === 'admin');
$isHighLevelDev = ($_SESSION['tipo'] === 'dev' && isset($_SESSION['nivel']) && $_SESSION['nivel'] >= 5);

if (!$isAdmin && !$isHighLevelDev) {
    echo "<script>alert('Acesso restrito a administradores e desenvolvedores nível 5+.'); window.location.href='login.php';</script>";
    exit;
}

// Cadastrar cliente
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome    = trim($_POST['nome']);
    $cpf     = preg_replace('/[^0-9]/', '', $_POST['cpf']);
    $email   = trim($_POST['email']);
    $empresa = trim($_POST['empresa']);
    $cargo   = trim($_POST['cargo']);
    $idade   = intval($_POST['idade']);

    // Validações básicas
    if (empty($nome) || empty($cpf) || empty($email) || empty($empresa) || empty($cargo) || $idade <= 0) {
        echo "<script>alert('Preencha todos os campos corretamente.');</script>";
    } else {
        // Verifica se já existe CPF
        $stmt = $conn->prepare("SELECT id FROM clientes WHERE cpf = ?");
        $stmt->bind_param("s", $cpf);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            echo "<script>alert('Já existe um cliente com esse CPF.');</script>";
        } else {
            // Inserir cliente
            $stmt = $conn->prepare("INSERT INTO clientes (nome, cpf, email, empresa, cargo, idade) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $nome, $cpf, $email, $empresa, $cargo, $idade);
            
            if ($stmt->execute()) {
                $cliente_id = $stmt->insert_id;
                $stmt->close();

                // Criar conta de acesso
                $senha_hash = password_hash($cpf, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO cliente_contas (cliente_id, login, senha) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $cliente_id, $email, $senha_hash);
                $stmt->execute();
                $stmt->close();

                echo "<script>alert('Cliente cadastrado com sucesso!'); window.location.href='lista_clientes.php';</script>";
                exit;
            } else {
                echo "<script>alert('Erro ao cadastrar cliente.');</script>";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Cliente</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-dark: #3a0ca3;
            --secondary-color: #4cc9f0;
            --danger-color: #f72585;
            --success-color: #4ad66d;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-light: #e9ecef;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--dark-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: white;
            width: 100%;
            max-width: 600px;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        h2 {
            color: var(--primary-dark);
            margin-bottom: 25px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--gray-light);
        }

        form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary-dark);
        }

        input, select {
            padding: 12px 15px;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        button[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 14px;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-align: center;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .back-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .admin-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: var(--danger-color);
            color: white;
            padding: 5px 10px;
            border-radius: var(--border-radius);
            font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($isAdmin): ?>
            <div class="admin-badge">
                <i class="fas fa-shield-alt"></i> Modo Administrador
            </div>
        <?php endif; ?>
        
        <h2><i class="fas fa-user-plus"></i> Cadastro de Cliente</h2>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nome"><i class="fas fa-user"></i> Nome Completo:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            
            <div class="form-group">
                <label for="cpf"><i class="fas fa-id-card"></i> CPF:</label>
                <input type="text" id="cpf" name="cpf" placeholder="Somente números" required>
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email (para login):</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="empresa"><i class="fas fa-building"></i> Empresa:</label>
                <input list="lista_empresas" id="empresa" name="empresa" required>
                <datalist id="lista_empresas">
                    <?php
                    $empresas = $conn->query("SELECT nome FROM empresas ORDER BY nome ASC");
                    while ($row = $empresas->fetch_assoc()) {
                        echo "<option value=\"" . htmlspecialchars($row['nome']) . "\">";
                    }
                    ?>
                </datalist>
            </div>
            
            <div class="form-group">
                <label for="cargo"><i class="fas fa-briefcase"></i> Cargo:</label>
                <input type="text" id="cargo" name="cargo" required>
            </div>
            
            <div class="form-group">
                <label for="idade"><i class="fas fa-birthday-cake"></i> Idade:</label>
                <input type="number" id="idade" name="idade" min="18" max="100" required>
            </div>
            
            <button type="submit"><i class="fas fa-save"></i> Cadastrar Cliente</button>
        </form>
        
        <a href="<?php echo $isAdmin ? 'index.php' : 'dashboard_dev.php'; ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
        </a>
    </div>

    <script>
        // Máscara para CPF
        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            e.target.value = value;
        });

        // Validação de idade
        document.getElementById('idade').addEventListener('change', function(e) {
            if (e.target.value < 18 || e.target.value > 100) {
                alert('Idade deve ser entre 18 e 100 anos.');
                e.target.value = '';
            }
        });
    </script>
</body>
</html>