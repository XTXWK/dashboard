<?php
include 'db_connect.php';
session_start();

// Verificação de acesso simplificada e segura
$acessoPermitido = false;

if (isset($_SESSION['tipo'])) {
    if ($_SESSION['tipo'] === 'admin') {
        $acessoPermitido = true;
    } elseif ($_SESSION['tipo'] === 'dev' && isset($_SESSION['nivel']) && $_SESSION['nivel'] >= 10) {
        $acessoPermitido = true;
    }
}

if (!$acessoPermitido) {
    echo "<script>alert('Acesso restrito!'); window.location.href='index.php';</script>";
    exit;
}

// Consulta SQL com tratamento de erros
$sql = "SELECT id, nome, email, nivel, funcao FROM devs ORDER BY nivel DESC";
$result = $conn->query($sql);

if ($result === false) {
    die("Erro na consulta: " . $conn->error);
}

// Verifica se há resultados
$temRegistros = ($result->num_rows > 0);
$isAdmin = ($_SESSION['tipo'] === 'admin');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Desenvolvedores</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-dark: #3a0ca3;
            --secondary-color: #4cc9f0;
            --danger-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-light: #e9ecef;
            --border-radius: 6px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        h2 {
            color: var(--primary-dark);
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
            font-size: 2rem;
            position: relative;
            padding-bottom: 10px;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
        }

        thead tr {
            background-color: var(--primary-color);
            color: white;
            text-align: left;
            font-weight: 600;
        }

        th, td {
            padding: 15px 20px;
        }

        tbody tr {
            border-bottom: 1px solid var(--gray-light);
            transition: var(--transition);
        }

        tbody tr:nth-of-type(even) {
            background-color: var(--light-color);
        }

        tbody tr:last-of-type {
            border-bottom: 2px solid var(--primary-color);
        }

        tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .btn {
            display: inline-block;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            margin-right: 8px;
            transition: var(--transition);
        }

        .btn.ver {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn.editar {
            background-color: var(--primary-color);
            color: white;
        }

        .btn.excluir {
            background-color: var(--danger-color);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
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
            font-weight: bold;
        }

        .add-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            text-decoration: none;
            transition: var(--transition);
        }

        .add-btn:hover {
            background-color: var(--primary-dark);
        }

        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
            }
            
            th, td {
                padding: 12px 15px;
            }
            
            .btn {
                padding: 6px 10px;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($isAdmin): ?>
            <div class="admin-badge">Modo Administrador</div>
        <?php endif; ?>
        
        <h2>Lista de Desenvolvedores</h2>

        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Nível</th>
                    <th>Função</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($temRegistros): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nome'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($row['email'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($row['nivel'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($row['funcao'] ?? ''); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a class="btn ver" href="ver_dev.php?id=<?= $row['id']; ?>">Ver</a>
                                    <a class="btn editar" href="editar_dev.php?id=<?= $row['id']; ?>">Editar</a>
                                    <?php if ($isAdmin || ($_SESSION['nivel'] > $row['nivel'])): ?>
                                        <a class="btn excluir" href="excluir_dev.php?id=<?= $row['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Nenhum desenvolvedor encontrado</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($isAdmin): ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="cadastrar_dev.php" class="add-btn">+ Adicionar Desenvolvedor</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>