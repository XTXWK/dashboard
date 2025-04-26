<?php
include 'db_connect.php';

$empresa = isset($_GET['empresa']) ? $_GET['empresa'] : '';

$stmt = $conn->prepare("SELECT * FROM clientes WHERE empresa = ?");
$stmt->bind_param("s", $empresa);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Clientes da Empresa</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f3f5;
            margin: 0;
            padding: 20px;
        }

        .container {
            background-color: white;
            max-width: 1000px;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #343a40;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .back-link:hover {
            background-color: #0056b3;
        }

        .empresa-nome {
            text-align: center;
            font-size: 18px;
            color: #555;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }

            th {
                position: sticky;
                top: 0;
            }

            td {
                padding: 10px;
                border: none;
                border-bottom: 1px solid #dee2e6;
            }

            td:before {
                content: attr(data-label);
                font-weight: bold;
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Clientes da Empresa</h2>
        <div class="empresa-nome">Empresa: <strong><?= htmlspecialchars($empresa); ?></strong></div>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Email</th>
                        <th>Cargo</th>
                        <th>Idade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($cliente = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($cliente['nome']); ?></td>
                            <td><?= htmlspecialchars($cliente['cpf']); ?></td>
                            <td><?= htmlspecialchars($cliente['email']); ?></td>
                            <td><?= htmlspecialchars($cliente['cargo']); ?></td>
                            <td><?= $cliente['idade']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum cliente encontrado para esta empresa.</p>
        <?php endif; ?>

        <a href="lista_empresas.php" class="back-link">← Voltar para a Lista de Empresas</a>
    </div>
</body>
</html>
