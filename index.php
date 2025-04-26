<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_logado = $_SESSION['usuario'] ?? 'Usuário';
$tipo_usuario = $_SESSION['tipo'] ?? 'usuario';

// Lógica de pesquisa
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT id, nome, cpf, empresa, cargo FROM clientes ";
if (!empty($search)) {
    $sql .= "WHERE nome LIKE '%$search%' OR cpf LIKE '%$search%' ";
}
$sql .= "ORDER BY id DESC LIMIT 10";
$result = $conn->query($sql);

// Total de clientes
$total_result = $conn->query("SELECT COUNT(*) AS total FROM clientes");
$total_clientes = $total_result->fetch_assoc()['total'] ?? 0;

// Último cliente
$ultimo_result = $conn->query("SELECT nome FROM clientes ORDER BY id DESC LIMIT 1");
$ultimo_cliente = $ultimo_result->fetch_assoc()['nome'] ?? 'Nenhum';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-dark: #3a0ca3;
            --secondary-color: #4cc9f0;
            --danger-color: #f72585;
            --success-color: #4ad66d;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-light: #e9ecef;
            --gray-medium: #adb5bd;
            --sidebar-width: 250px;
            --header-height: 60px;
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
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            color: white;
            padding: 20px 0;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            z-index: 1000;
        }

        .sidebar h2 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            margin: 5px 0;
            border-radius: 4px;
            transition: var(--transition);
        }

        .sidebar a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .logo-bottom {
            position: absolute;
            bottom: 20px;
            width: 100%;
            padding: 0 20px;
        }

        .logo-bottom img {
            width: 100%;
            max-width: 200px;
            display: block;
            margin: 0 auto;
            filter: brightness(0) invert(1);
        }

        /* Main Content */
        .content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Header */
        .header {
            background-color: white;
            padding: 0 20px;
            height: var(--header-height);
            display: flex;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .container-header {
            width: 100%;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        /* Main Container */
        .main-container {
            padding: 20px;
        }

        /* Cards */
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card-info {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: var(--transition);
        }

        .card-info:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-info i {
            font-size: 2rem;
            margin-right: 15px;
            color: var(--primary-color);
        }

        .card-info .text h4 {
            color: var(--gray-medium);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .card-info .text p {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .card-btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: var(--transition);
        }

        .card-btn:hover {
            background-color: var(--primary-dark);
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .table-container h2 {
            color: var(--primary-dark);
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .search-bar form {
            display: flex;
        }

        .search-bar input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid var(--gray-light);
            border-radius: 4px 0 0 4px;
            outline: none;
            transition: var(--transition);
        }

        .search-bar input:focus {
            border-color: var(--primary-color);
        }

        .search-bar button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-bar button:hover {
            background-color: var(--primary-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        table th {
            background-color: var(--light-color);
            color: var(--primary-dark);
            font-weight: 600;
        }

        table tr:hover {
            background-color: var(--light-color);
        }

        .view-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .view-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Charts */
        .chart-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .chart-card h3 {
            color: var(--primary-dark);
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }

            .sidebar h2, .sidebar a span, .logo-bottom {
                display: none;
            }

            .sidebar a {
                text-align: center;
                padding: 15px 5px;
            }

            .sidebar a i {
                margin-right: 0;
                font-size: 1.2rem;
            }

            .content {
                margin-left: 70px;
            }

            .chart-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .info-cards {
                grid-template-columns: 1fr;
            }

            .search-bar form {
                flex-direction: column;
            }

            .search-bar input {
                border-radius: 4px;
                margin-bottom: 5px;
            }

            .search-bar button {
                border-radius: 4px;
            }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Painel</h2>
    <?php if ($tipo_usuario === 'admin'): ?>
        <a href="cadastro_cliente.php"><i class="fa-solid fa-user-plus"></i> <span>Cadastrar Cliente</span></a>
        <a href="cadastro_exame.php"><i class="fa-solid fa-notes-medical"></i> <span>Cadastrar Exame</span></a>
        <a href="cadastro_empresa.php"><i class="fa-solid fa-building"></i> <span>Cadastrar Empresa</span></a>
        <a href="cadastrar_dev.php"><i class="fa-solid fa-user-gear"></i> <span>Cadastrar devs</span></a>
    <?php endif; ?>
    <a href="lista_clientes.php"><i class="fa-solid fa-list"></i> <span>Listar Clientes</span></a>
    <a href="lista_exame.php"><i class="fa-solid fa-file-medical"></i> <span>Lista de Exames</span></a>
    <a href="lista_empresas.php"><i class="fa-solid fa-list"></i> <span>Listar Empresas</span></a>
    <a href="lista_admin.php"><i class="fa-solid fa-list"></i> <span>Listar Admins</span></a>
    <a href="lista_devs.php"><i class="fa-solid fa-list"></i> <span>Listar devs</span></a>

    <div class="logo-bottom">
        <img src="https://static.wixstatic.com/media/76a495_35db08a05b824567b3f5c31ed5e70222~mv2.png/v1/fill/w_552,h_194,al_c,lg_1,q_85,enc_avif,quality_auto/LOGO%20BRANCA.png" alt="Logo da Empresa">
    </div>
</div>

<div class="content">
    <div class="header">
        <div class="container-header">
            Dashboard - Gerenciamento do Sistema
            <div style="float: right; font-size: 14px;">
                Olá, <?php echo htmlspecialchars($usuario_logado); ?> |
                <a href="logout.php" style="color: var(--primary-color); text-decoration: none;">Sair</a>
            </div>
        </div>
    </div>

    <div class="main-container">
        <!-- Cards de Informação -->
        <div class="info-cards">
            <div class="card-info">
                <i class="fa-solid fa-users"></i>
                <div class="text">
                    <h4>Total de Clientes</h4>
                    <p><?php echo $total_clientes; ?></p>
                </div>
            </div>
            <div class="card-info">
                <i class="fa-solid fa-user-clock"></i>
                <div class="text">
                    <h4>Último Cliente</h4>
                    <p><?php echo htmlspecialchars($ultimo_cliente); ?></p>
                </div>
            </div>
            <?php if ($tipo_usuario === 'admin'): ?>
            <div class="card-info">
                <i class="fa-solid fa-user-plus"></i>
                <div class="text">
                    <h4>Novo Cadastro</h4>
                    <a href="cadastro_cliente.php" class="card-btn">Cadastrar</a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Gráficos -->
        <div class="chart-container">
            <div class="chart-card">
                <h3>Clientes por Empresa</h3>
                <div class="chart-wrapper">
                    <canvas id="empresaChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h3>Status de Exames</h3>
                <div class="chart-wrapper">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabela de Clientes -->
        <div class="table-container">
            <h2>Últimos Clientes Cadastrados</h2>
            <div class="search-bar">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Pesquisar por Nome ou CPF" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Buscar</button>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Empresa</th>
                        <th>Cargo</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['nome']); ?></td>
                            <td><?php echo htmlspecialchars($row['cpf']); ?></td>
                            <td><?php echo htmlspecialchars($row['empresa']); ?></td>
                            <td><?php echo htmlspecialchars($row['cargo']); ?></td>
                            <td><a class="view-link" href="detalhes_cliente.php?id=<?php echo $row['id']; ?>"><i class="fas fa-eye"></i> Ver detalhes</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Gráfico de Clientes por Empresa (dados fictícios - substitua pelos seus dados reais)
    const empresaCtx = document.getElementById('empresaChart').getContext('2d');
    const empresaChart = new Chart(empresaCtx, {
        type: 'bar',
        data: {
            labels: ['Empresa A', 'Empresa B', 'Empresa C', 'Empresa D', 'Outros'],
            datasets: [{
                label: 'Clientes por Empresa',
                data: [12, 19, 8, 5, 3],
                backgroundColor: [
                    'rgba(67, 97, 238, 0.7)',
                    'rgba(76, 201, 240, 0.7)',
                    'rgba(247, 37, 133, 0.7)',
                    'rgba(74, 214, 109, 0.7)',
                    'rgba(255, 193, 7, 0.7)'
                ],
                borderColor: [
                    'rgba(67, 97, 238, 1)',
                    'rgba(76, 201, 240, 1)',
                    'rgba(247, 37, 133, 1)',
                    'rgba(74, 214, 109, 1)',
                    'rgba(255, 193, 7, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfico de Status de Exames (dados fictícios - substitua pelos seus dados reais)
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pendentes', 'Agendados', 'Realizados', 'Cancelados'],
            datasets: [{
                data: [15, 8, 22, 3],
                backgroundColor: [
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(23, 162, 184, 0.7)',
                    'rgba(74, 214, 109, 0.7)',
                    'rgba(247, 37, 133, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 193, 7, 1)',
                    'rgba(23, 162, 184, 1)',
                    'rgba(74, 214, 109, 1)',
                    'rgba(247, 37, 133, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
</script>
</body>
</html>
