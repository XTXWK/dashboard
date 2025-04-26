<?php
include 'db_connect.php';
session_start();

// Verifica se o usuário está logado e pega o tipo de usuário
$tipo_usuario = $_SESSION['tipo'] ?? '';

// Exclusão de empresa se id for passado por GET
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM empresas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: lista_empresas.php");
    exit();
}

// Busca todas as empresas cadastradas
$sql = "SELECT id, nome, cnpj FROM empresas ORDER BY nome ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Empresas</title>
    <link rel="stylesheet" href="css/lista_empresas.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f4f8;
            padding: 30px;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            max-width: 1000px;
            margin: auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        .btn-excluir {
            background-color: #dc3545;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .btn-excluir:hover {
            background-color: #c82333;
        }

        .btn-editar {
            background-color: #ffc107;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .btn-editar:hover {
            background-color: #e0a800;
        }

        .btn-clientes {
            background-color: #17a2b8;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .btn-clientes:hover {
            background-color: #138496;
        }

        .search-box {
            text-align: right;
            margin-bottom: 10px;
        }

        .search-box input {
            padding: 8px;
            width: 250px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        a.back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function pesquisarEmpresa() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let linhas = document.querySelectorAll('table tbody tr');

            linhas.forEach(linha => {
                let nome = linha.querySelector('td:nth-child(2)').textContent.toLowerCase();
                let cnpj = linha.querySelector('td:nth-child(3)').textContent.toLowerCase();
                linha.style.display = nome.includes(input) || cnpj.includes(input) ? '' : 'none';
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Lista de Empresas Cadastradas</h2>

        <div class="search-box">
            <input type="text" id="searchInput" onkeyup="pesquisarEmpresa()" placeholder="Pesquisar por nome ou CNPJ...">
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome da Empresa</th>
                    <th>CNPJ</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['nome']); ?></td>
                            <td><?= htmlspecialchars($row['cnpj']); ?></td>
                            <td class="action-buttons">
                                <a href="?delete_id=<?= $row['id']; ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir esta empresa?');">Excluir</a>
                                <a href="editar_empresa.php?id=<?= $row['id']; ?>" class="btn-editar">Editar</a>
                                <a href="clientes_por_empresa.php?id=<?= $row['id']; ?>" class="btn-clientes">Ver Clientes</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Nenhuma empresa cadastrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Alteração do link de volta ao dashboard -->
        <a href="<?= ($tipo_usuario === 'admin') ? 'index.php' : 'dashboard_dev.php' ?>" class="back-link">&larr; Voltar ao Dashboard</a>
    </div>
</body>
</html>
