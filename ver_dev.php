<?php
include 'db_connect.php';
session_start();

// Verificação de permissão para admin ou dev nível 10+
if (!isset($_SESSION['tipo']) || 
   ($_SESSION['tipo'] !== 'admin' && ($_SESSION['tipo'] !== 'dev' || !isset($_SESSION['nivel']) || $_SESSION['nivel'] < 10))) {
    echo "<script>alert('Acesso negado.'); window.location.href='index.php';</script>";
    exit;
}

// Verifica se o ID foi passado
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "<script>alert('ID inválido.'); window.location.href='lista_devs.php';</script>";
    exit;
}

// Processamento do formulário
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $nivel = isset($_POST['nivel']) ? (int)$_POST['nivel'] : 0;
    $funcao = isset($_POST['funcao']) ? trim($_POST['funcao']) : '';

    // Se for admin, pode alterar qualquer campo
    // Se for dev nível 10+, não pode alterar o nível para maior que o seu
    if ($_SESSION['tipo'] === 'dev' && isset($_SESSION['nivel']) && $nivel > $_SESSION['nivel']) {
        echo "<script>alert('Você não pode definir um nível maior que o seu.');</script>";
    } else {
        $stmt = $conn->prepare("UPDATE devs SET nome=?, email=?, nivel=?, funcao=? WHERE id=?");
        $stmt->bind_param("ssisi", $nome, $email, $nivel, $funcao, $id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Dev atualizado com sucesso!'); window.location.href='lista_devs.php';</script>";
        } else {
            echo "<script>alert('Erro ao atualizar dev.');</script>";
        }
        exit;
    }
}

// Busca os dados do desenvolvedor
$stmt = $conn->prepare("SELECT nome, email, nivel, funcao FROM devs WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$dev = $result->fetch_assoc();

// Verifica se encontrou o desenvolvedor
if (!$dev) {
    echo "<script>alert('Dev não encontrado.'); window.location.href='lista_devs.php';</script>";
    exit;
}

$isAdmin = ($_SESSION['tipo'] === 'admin');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Visualizar Dev</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4cc9f0;
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
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .dev-details {
            margin: 25px 0;
        }
        
        .dev-details p {
            margin-bottom: 15px;
            padding: 12px 15px;
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            display: flex;
        }
        
        .dev-details strong {
            min-width: 100px;
            display: inline-block;
            color: var(--secondary-color);
        }
        
        a {
            display: inline-block;
            text-decoration: none;
            color: white;
            background-color: var(--primary-color);
            padding: 10px 20px;
            border-radius: var(--border-radius);
            margin-top: 10px;
            transition: var(--transition);
        }
        
        a:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 20px 10px;
            }
            
            .dev-details p {
                flex-direction: column;
            }
            
            .dev-details strong {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Detalhes do Desenvolvedor</h2>
        <?php if(isset($dev) && is_array($dev)): ?>
            <div class="dev-details">
                <p><strong>Nome:</strong> <?= htmlspecialchars($dev['nome'] ?? 'Não informado') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($dev['email'] ?? 'Não informado') ?></p>
                <p><strong>Nível:</strong> <?= $dev['nivel'] ?? 'Não informado' ?></p>
                <p><strong>Função:</strong> <?= htmlspecialchars($dev['funcao'] ?? 'Não informado') ?></p>
            </div>
        <?php else: ?>
            <p>Desenvolvedor não encontrado.</p>
        <?php endif; ?>
        <a href="lista_devs.php">Voltar</a>
    </div>
</body>
</html>