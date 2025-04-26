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
    <title>Editar Dev</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --success-color: #4cc9f0;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            width: 100%;
            max-width: 600px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
            margin: 2rem;
        }
        
        h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--secondary-color);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="number"] {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="number"]:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            gap: 1rem;
        }
        
        button[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: var(--transition);
            flex: 1;
        }
        
        button[type="submit"]:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .cancel-btn {
            display: inline-block;
            text-align: center;
            background-color: var(--gray-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            flex: 1;
        }
        
        .cancel-btn:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Editar Desenvolvedor</h2>
        <form method="post">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?= isset($dev['nome']) ? htmlspecialchars($dev['nome']) : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= isset($dev['email']) ? htmlspecialchars($dev['email']) : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="nivel">Nível (0-10)</label>
                <input type="number" id="nivel" name="nivel" value="<?= isset($dev['nivel']) ? $dev['nivel'] : 0 ?>" min="0" max="10" required>
            </div>
            
            <div class="form-group">
                <label for="funcao">Função</label>
                <input type="text" id="funcao" name="funcao" value="<?= isset($dev['funcao']) ? htmlspecialchars($dev['funcao']) : '' ?>">
            </div>
            
            <div class="button-group">
                <button type="submit">Salvar Alterações</button>
                <a href="lista_devs.php" class="cancel-btn">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>