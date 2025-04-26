<?php
include 'db_connect.php';

$sql = "SELECT id, nome, email FROM admins ORDER BY nome ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Administradores</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f9;
            padding: 30px;
        }

        .container {
            background-color: white;
            padding: 30px;
            max-width: 800px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        .search-box {
            text-align: right;
            margin-bottom: 10px;
        }

        .search-box input {
            padding: 8px;
            width: 260px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .actions a {
            text-decoration: none;
            padding: 6px 12px;
            margin-right: 5px;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-editar {
            background-color: #ffc107;
            color: white;
        }

        .btn-excluir {
            background-color: #dc3545;
            color: white;
        }

        .btn-editar:hover {
            background-color: #e0a800;
        }

        .btn-excluir:hover {
            background-color: #c82333;
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
    </style>
    <script>
        function pesquisarAdmin() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let linhas = document.querySelectorAll("table tbody tr");

            linhas.forEach(linha => {
                let nome = linha.querySelector("td:nth-child(2)").textContent.toLowerCase();
                let email = linha.querySelector("td:nth-child(3)").textContent.toLowerCase();
                linha.style.display = nome.includes(input) || email.includes(input) ? '' : 'none';
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Lista de Administradores</h2>

        <div class="search-box">
            <input type="text" id="searchInput" onkeyup="pesquisarAdmin()" placeholder="Pesquisar por nome ou email...">
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['nome']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td class="actions">
                                <a href="editar_admin.php?id=<?= $row['id']; ?>" class="btn-editar">Editar</a>
                                <a href="excluir_admin.php?id=<?= $row['id']; ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir este administrador?')">Excluir</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">Nenhum administrador encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="index.php" class="voltar">← Voltar ao Dashboard</a>
    </div>
</body>
</html>
