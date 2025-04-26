<?php
include 'db_connect.php';
session_start();

// Verificação de autenticação
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Definição de variáveis de sessão com valores padrão
$tipoUsuario = $_SESSION['tipo'] ?? '';
$nivelDev = $_SESSION['nivel'] ?? 0;

// Verificação de tipos de usuário
$isAdmin = ($tipoUsuario === 'admin');
$isUser = ($tipoUsuario === 'usuario');
$isCliente = ($tipoUsuario === 'cliente');
$isDev = ($tipoUsuario === 'dev');

// Verificação de níveis de dev
$isDevNivel1 = ($isDev && $nivelDev == 1);
$isDevNivel2 = ($isDev && $nivelDev == 2);
$isDevNivel3 = ($isDev && $nivelDev == 3);
$isDevNivel10 = ($isDev && $nivelDev == 10);

// Verifica se o ID do cliente foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do cliente não fornecido.");
}

$cliente_id = intval($_GET['id']);

// Segurança: cliente só pode acessar seus próprios dados
if ($isCliente && ($_SESSION['cliente_id'] ?? 0) != $cliente_id) {
    die("Acesso negado.");
}

// Buscar informações do cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cliente) {
    die("Cliente não encontrado.");
}

// Buscar exames associados ao cliente com status
$stmt = $conn->prepare("SELECT exames.id, exames.nome, cliente_exames.status, 
                        cliente_exames.data_realizacao, cliente_exames.observacoes 
                        FROM exames 
                        INNER JOIN cliente_exames ON exames.id = cliente_exames.exame_id 
                        WHERE cliente_exames.cliente_id = ?");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$exames = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Buscar documentos associados
$documentos = array();
$documentos_dir = "uploads/clientes/" . $cliente_id . "/";
if (is_dir($documentos_dir)) {
    $documentos = array_diff(scandir($documentos_dir), array('..', '.'));
}

// Remover exame associado (somente admin ou devs de nível >= 3)
if (($isAdmin || $nivelDev >= 3) && isset($_GET['remove_exame_id'])) {
    $remove_exame_id = intval($_GET['remove_exame_id']);
    $stmt = $conn->prepare("DELETE FROM cliente_exames WHERE cliente_id = ? AND exame_id = ?");
    $stmt->bind_param("ii", $cliente_id, $remove_exame_id);
    if ($stmt->execute()) {
        echo "<script>alert('Exame removido com sucesso!'); window.location.href='detalhes_cliente.php?id=$cliente_id';</script>";
    }
    $stmt->close();
}

// Atualizar status do exame (somente admin ou devs de nível >= 2)
if (($isAdmin || $nivelDev >= 2) && isset($_POST['atualizar_status'])) {
    $exame_id = intval($_POST['exame_id']);
    $novo_status = $_POST['novo_status'];
    $data_realizacao = !empty($_POST['data_realizacao']) ? $_POST['data_realizacao'] : null;
    $observacoes = !empty($_POST['observacoes']) ? $_POST['observacoes'] : null;

    $stmt = $conn->prepare("UPDATE cliente_exames 
                           SET status = ?, data_realizacao = ?, observacoes = ?
                           WHERE cliente_id = ? AND exame_id = ?");
    $stmt->bind_param("sssii", $novo_status, $data_realizacao, $observacoes, $cliente_id, $exame_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Status do exame atualizado!'); window.location.href='detalhes_cliente.php?id=$cliente_id';</script>";
    }
    $stmt->close();
}

// Remover documento (somente admin ou devs de nível >= 3)
if (($isAdmin || $nivelDev >= 3) && isset($_GET['remove_documento'])) {
    $remove_documento = $_GET['remove_documento'];
    $file_path = $documentos_dir . $remove_documento;
    if (file_exists($file_path)) {
        unlink($file_path);
        echo "<script>alert('Documento removido com sucesso!'); window.location.href='detalhes_cliente.php?id=$cliente_id';</script>";
    }
}

// Excluir cliente (somente admin ou dev nível 10)
if (($isAdmin || $nivelDev == 10) && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir'])) {
    $idExcluir = $_POST['excluir_id'];

    $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $idExcluir);
    $stmt->execute();

    header("Location: lista_clientes.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Cliente</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --danger-color: #dc3545;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --gray-color: #6c757d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #212529;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        h2, h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .info-section {
            margin-bottom: 30px;
            padding: 20px;
            background: var(--light-color);
            border-radius: 8px;
        }
        
        .info-section p {
            margin-bottom: 10px;
        }
        
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 0.9em;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
        }
        
        .styled-table thead tr {
            background-color: var(--primary-color);
            color: white;
            text-align: left;
        }
        
        .styled-table th,
        .styled-table td {
            padding: 12px 15px;
        }
        
        .styled-table tbody tr {
            border-bottom: 1px solid #dddddd;
        }
        
        .styled-table tbody tr:nth-of-type(even) {
            background-color: #f3f3f3;
        }
        
        .styled-table tbody tr:last-of-type {
            border-bottom: 2px solid var(--primary-color);
        }
        
        .styled-table tbody tr:hover {
            background-color: #f1f1f1;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            margin-right: 10px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-remove {
            background-color: var(--danger-color);
        }
        
        .btn-edit {
            background-color: var(--primary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
        }
        
        .btn-info {
            background-color: var(--info-color);
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            color: white;
            display: inline-block;
        }
        
        .status-pendente {
            background-color: var(--warning-color);
        }
        
        .status-agendado {
            background-color: var(--info-color);
        }
        
        .status-realizado {
            background-color: var(--success-color);
        }
        
        .status-cancelado {
            background-color: var(--danger-color);
        }
        
        form {
            display: inline;
        }
        
        select, input[type="date"], textarea {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 2px 0;
        }
        
        textarea {
            width: 100%;
            min-height: 60px;
        }
        
        button[type="submit"] {
            padding: 10px 20px;
            background-color: var(--danger-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        button[type="submit"]:hover {
            background-color: #c82333;
        }
        
        .back-link {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--gray-color);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .back-link:hover {
            background-color: #5a6268;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .styled-table {
                display: block;
                overflow-x: auto;
            }
            
            .btn {
                display: block;
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($isAdmin): ?>
            <div style="position: absolute; top: 20px; right: 20px; background-color: var(--danger-color); color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.8em;">
                Modo Administrador
            </div>
        <?php endif; ?>
        
        <h2>Detalhes do Cliente</h2>
        
        <div class="info-section">
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($cliente['nome']); ?></p>
            <p><strong>CPF:</strong> <?php echo htmlspecialchars($cliente['cpf']); ?></p>
            <p><strong>Empresa:</strong> <?php echo htmlspecialchars($cliente['empresa']); ?></p>
            <p><strong>Cargo:</strong> <?php echo htmlspecialchars($cliente['cargo']); ?></p>
            <p><strong>Idade:</strong> <?php echo htmlspecialchars($cliente['idade']); ?></p>
        </div>

        <div class="info-section">
            <h3>Exames Necessários</h3>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Status</th>
                        <th>Data Realização</th>
                        <th>Observações</th>
                        <?php if ($isAdmin || $nivelDev >= 3): ?>
                        <th>Ação</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($exames) > 0): ?>
                        <?php foreach ($exames as $exame): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($exame['id']); ?></td>
                            <td><?php echo htmlspecialchars($exame['nome']); ?></td>
                            <td>
                                <?php if ($isAdmin || $nivelDev >= 2): ?>
                                    <form method="post">
                                        <input type="hidden" name="exame_id" value="<?php echo $exame['id']; ?>">
                                        <select name="novo_status" onchange="this.form.submit()">
                                            <option value="pendente" <?php echo $exame['status'] == 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                            <option value="agendado" <?php echo $exame['status'] == 'agendado' ? 'selected' : ''; ?>>Agendado</option>
                                            <option value="realizado" <?php echo $exame['status'] == 'realizado' ? 'selected' : ''; ?>>Realizado</option>
                                            <option value="cancelado" <?php echo $exame['status'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                        </select>
                                        <input type="hidden" name="atualizar_status" value="1">
                                    </form>
                                <?php else: ?>
                                    <span class="status-badge status-<?php echo htmlspecialchars($exame['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($exame['status'])); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isAdmin || $nivelDev >= 2): ?>
                                    <form method="post">
                                        <input type="hidden" name="exame_id" value="<?php echo $exame['id']; ?>">
                                        <input type="date" name="data_realizacao" 
                                               value="<?php echo htmlspecialchars($exame['data_realizacao'] ?? ''); ?>"
                                               onchange="this.form.submit()">
                                        <input type="hidden" name="atualizar_status" value="1">
                                    </form>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($exame['data_realizacao'] ?? '-'); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isAdmin || $nivelDev >= 2): ?>
                                    <form method="post">
                                        <input type="hidden" name="exame_id" value="<?php echo $exame['id']; ?>">
                                        <textarea name="observacoes" 
                                                  onchange="this.form.submit()"
                                                  style="width: 100%; min-height: 40px;"><?php echo htmlspecialchars($exame['observacoes'] ?? ''); ?></textarea>
                                        <input type="hidden" name="atualizar_status" value="1">
                                    </form>
                                <?php else: ?>
                                    <?php echo nl2br(htmlspecialchars($exame['observacoes'] ?? '-')); ?>
                                <?php endif; ?>
                            </td>
                            <?php if ($isAdmin || $nivelDev >= 3): ?>
                            <td>
                                <a href="detalhes_cliente.php?id=<?php echo $cliente_id; ?>&remove_exame_id=<?php echo $exame['id']; ?>" 
                                   class="btn btn-remove"
                                   onclick="return confirm('Tem certeza que deseja remover este exame?');">
                                   <i class="fas fa-trash"></i> Remover
                                </a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo ($isAdmin || $nivelDev >= 3) ? '6' : '5'; ?>" style="text-align: center;">
                                Nenhum exame cadastrado para este cliente.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="info-section">
            <h3>Documentos Associados</h3>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Nome do Documento</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($documentos) > 0): ?>
                        <?php foreach ($documentos as $documento): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($documento); ?></td>
                            <td>
                                <a href="<?php echo $documentos_dir . $documento; ?>" 
                                   target="_blank" 
                                   class="btn btn-info">
                                   <i class="fas fa-eye"></i> Visualizar
                                </a>
                                <?php if ($isAdmin || $nivelDev >= 3): ?>
                                <a href="detalhes_cliente.php?id=<?php echo $cliente_id; ?>&remove_documento=<?php echo urlencode($documento); ?>" 
                                   class="btn btn-remove"
                                   onclick="return confirm('Tem certeza que deseja remover este documento?');">
                                   <i class="fas fa-trash"></i> Remover
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                Nenhum documento associado a este cliente.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            <?php if ($isAdmin || $nivelDev >= 3): ?>
                <a href="adicionar_documento.php?id=<?php echo $cliente_id; ?>" class="btn btn-success">
                    <i class="fas fa-plus"></i> Adicionar Documento
                </a>
                <a href="adicionar-exame.php?id=<?php echo $cliente_id; ?>" class="btn btn-success">
                    <i class="fas fa-plus"></i> Adicionar Exame
                </a>
            <?php endif; ?>

            <?php if ($isAdmin || $nivelDev == 10): ?>
                <a href="editar_cliente.php?id=<?php echo $cliente['id']; ?>" class="btn btn-edit">
                    <i class="fas fa-edit"></i> Editar Cliente
                </a>
                <form method="post" onsubmit="return confirm('Tem certeza que deseja excluir este cliente?');" style="display: inline;">
                    <input type="hidden" name="excluir_id" value="<?php echo $cliente['id']; ?>">
                    <button type="submit" name="excluir" class="btn btn-remove">
                        <i class="fas fa-trash"></i> Excluir Cliente
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px;">
            <?php if ($isAdmin): ?>
                <a href="index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                </a>
            <?php elseif ($isDevNivel10): ?>
                <a href="dashboard_dev.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Voltar ao Dashboard de Dev
                </a>
            <?php elseif ($isDev || $isUser): ?>
                <a href="index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>