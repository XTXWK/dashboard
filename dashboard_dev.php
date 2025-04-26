<?php
include 'db_connect.php';
session_start();

// Verificar se é um desenvolvedor logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar se o usuário é um dev
if ($_SESSION['tipo'] !== 'dev') {
    echo "<script>alert('Acesso restrito a desenvolvedores.'); window.location.href='login.php';</script>";
    exit;
}

$nivel_dev = $_SESSION['nivel'] ?? 0;
$usuario_logado = $_SESSION['usuario'] ?? 'Desenvolvedor';

// Obter estatísticas baseadas no nível do dev
$total_clientes = 0;
$total_exames = 0;
$clientes_ativos = 0;

if ($nivel_dev >= 5) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM clientes");
    $total_clientes = $result->fetch_assoc()['total'] ?? 0;
    
    $result = $conn->query("SELECT COUNT(*) AS total FROM exames");
    $total_exames = $result->fetch_assoc()['total'] ?? 0;
    
    // REMOVIDO O FILTRO POR STATUS QUE NÃO EXISTE
    $result = $conn->query("SELECT COUNT(*) AS total FROM clientes");
    $clientes_ativos = $result->fetch_assoc()['total'] ?? 0;
}

// ... (restante do código permanece igual)
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Desenvolvedor</title>
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

        .dev-level {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            font-size: 0.9rem;
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

        /* Tables */
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .table-container h3 {
            color: var(--primary-dark);
            margin-bottom: 20px;
            font-size: 1.3rem;
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
            height: 250px;
            width: 100%;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }

            .sidebar h2, .sidebar a span, .dev-level, .logo-bottom {
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
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Painel Dev</h2>
    <a href="dashboard_dev.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
    
    <?php if ($nivel_dev >= 1): ?>
        <a href="lista_clientes.php"><i class="fas fa-users"></i> <span>Clientes</span></a>
    <?php endif; ?>
    <?php if ($nivel_dev >= 2): ?>
        <a href="lista_exame.php"><i class="fas fa-file-medical"></i> <span>Exames</span></a>
    <?php endif; ?>
    <?php if ($nivel_dev >= 3): ?>
        <a href="lista_empresas.php"><i class="fas fa-file-medical"></i> <span>Empresas</span></a>
    <?php endif; ?>
    <?php if ($nivel_dev >= 4): ?>
        <a href="cadastro_exame.php"><i class="fas fa-user-plus"></i> <span>Cadastrar Exame</span></a>
    <?php endif; ?>
    
    <?php if ($nivel_dev >= 5): ?>
        <a href="cadastro_empresa.php"><i class="fas fa-user-plus"></i> <span>Cadastrar Empresa</span></a>
    <?php endif; ?>
    <?php if ($nivel_dev >= 5): ?>
        <a href="cadastro_cliente.php"><i class="fas fa-user-plus"></i> <span>Cadastrar Cliente</span></a>
    <?php endif; ?>
    
    <a href="meu_perfil.php"><i class="fas fa-user-cog"></i> <span>Meu Perfil</span></a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a>
    
    <span class="dev-level">Nível <?php echo $nivel_dev; ?> Dev</span>
    
    <div class="logo-bottom">
        <img src="https://static.wixstatic.com/media/76a495_35db08a05b824567b3f5c31ed5e70222~mv2.png/v1/fill/w_552,h_194,al_c,lg_1,q_85,enc_avif,quality_auto/LOGO%20BRANCA.png" alt="Logo da Empresa">
    </div>
</div>

<div class="content">
    <div class="header">
        <div class="container-header">
            Dashboard - Desenvolvedor
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
                <i class="fas fa-code"></i>
                <div class="text">
                    <h4>Seu Nível</h4>
                    <p><?php echo $nivel_dev; ?></p>
                </div>
            </div>
            
            <?php if ($nivel_dev >= 5): ?>
            <div class="card-info">
                <i class="fas fa-users"></i>
                <div class="text">
                    <h4>Total Clientes</h4>
                    <p><?php echo $total_clientes; ?></p>
                </div>
            </div>
            
            <div class="card-info">
                <i class="fas fa-file-medical"></i>
                <div class="text">
                    <h4>Total Exames</h4>
                    <p><?php echo $total_exames; ?></p>
                </div>
            </div>
            
            <div class="card-info">
                <i class="fas fa-user-check"></i>
                <div class="text">
                    <h4>Clientes Ativos</h4>
                    <p><?php echo $clientes_ativos; ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Gráficos -->
        <?php if ($nivel_dev >= 5): ?>
        <div class="chart-container">
            <div class="chart-card">
                <h3><i class="fas fa-chart-bar"></i> Atividades Recentes</h3>
                <div class="chart-wrapper">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Distribuição de Acessos</h3>
                <div class="chart-wrapper">
                    <canvas id="accessChart"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabelas de Informação -->
        <?php if ($nivel_dev >= 3 && !empty($ultimos_clientes)): ?>
        <div class="table-container">
            <h3><i class="fas fa-users"></i> Últimos Clientes Cadastrados</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Empresa</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimos_clientes as $cliente): ?>
                    <tr>
                        <td><?php echo $cliente['id']; ?></td>
                        <td><?php echo htmlspecialchars($cliente['nome']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['cpf']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['empresa']); ?></td>
                        <td><a href="detalhes_cliente.php?id=<?php echo $cliente['id']; ?>" class="view-link">Ver</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if ($nivel_dev >= 4 && !empty($ultimos_exames)): ?>
        <div class="table-container">
            <h3><i class="fas fa-file-medical"></i> Últimos Exames Cadastrados</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimos_exames as $exame): ?>
                    <tr>
                        <td><?php echo $exame['id']; ?></td>
                        <td><?php echo htmlspecialchars($exame['nome']); ?></td>
                        <td><?php echo htmlspecialchars($exame['tipo']); ?></td>
                        <td><a href="detalhes_exame.php?id=<?php echo $exame['id']; ?>" class="view-link">Ver</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Gráfico de Atividades (dados fictícios - substitua pelos seus dados reais)
    <?php if ($nivel_dev >= 5): ?>
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Clientes Cadastrados',
                data: [12, 19, 8, 15, 12, 18],
                backgroundColor: 'rgba(67, 97, 238, 0.2)',
                borderColor: 'rgba(67, 97, 238, 1)',
                borderWidth: 2,
                tension: 0.3
            }, {
                label: 'Exames Realizados',
                data: [8, 12, 6, 10, 15, 12],
                backgroundColor: 'rgba(76, 201, 240, 0.2)',
                borderColor: 'rgba(76, 201, 240, 1)',
                borderWidth: 2,
                tension: 0.3
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

    // Gráfico de Distribuição de Acessos
    const accessCtx = document.getElementById('accessChart').getContext('2d');
    const accessChart = new Chart(accessCtx, {
        type: 'doughnut',
        data: {
            labels: ['Clientes', 'Exames', 'Configurações', 'Relatórios'],
            datasets: [{
                data: [45, 30, 15, 10],
                backgroundColor: [
                    'rgba(67, 97, 238, 0.7)',
                    'rgba(76, 201, 240, 0.7)',
                    'rgba(247, 37, 133, 0.7)',
                    'rgba(74, 214, 109, 0.7)'
                ],
                borderColor: [
                    'rgba(67, 97, 238, 1)',
                    'rgba(76, 201, 240, 1)',
                    'rgba(247, 37, 133, 1)',
                    'rgba(74, 214, 109, 1)'
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
    <?php endif; ?>
</script>
</body>
</html>