<?php
include 'db_connect.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do cliente não fornecido.");
}

$cliente_id = intval($_GET['id']);
$cliente_pasta = "uploads/clientes/$cliente_id/";

// Criar a pasta do cliente se não existir
if (!is_dir($cliente_pasta)) {
    mkdir($cliente_pasta, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['documento'])) {
    $arquivo = $_FILES['documento'];
    $nome_arquivo = basename($arquivo['name']);
    $caminho_destino = $cliente_pasta . $nome_arquivo;
    
    if (move_uploaded_file($arquivo['tmp_name'], $caminho_destino)) {
        echo "<script>alert('Documento enviado com sucesso!'); window.location.href='adicionar_documento.php?id=$cliente_id';</script>";
    } else {
        echo "Erro ao enviar documento.";
    }
}

// Listar documentos existentes
$documentos = array_diff(scandir($cliente_pasta), array('.', '..'));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Documento</title>
    <link rel="stylesheet" href="css/adicionar_documento.css">
    
</head>
<body>
    <div class="container">
        <h2>Adicionar Documento para Cliente</h2>
        <form method="POST" enctype="multipart/form-data">
            <label for="documento">Escolha um documento:</label>
            <input type="file" name="documento" id="documento" required>
            <button type="submit">Enviar Documento</button>
        </form>
        
        <h3>Documentos Enviados</h3>
        <ul>
            <?php foreach ($documentos as $doc) {
                echo "<li><a href='$cliente_pasta$doc' target='_blank'>$doc</a></li>";
            } ?>
        </ul>
        
        <a href="detalhes_cliente.php?id=<?php echo $cliente_id; ?>" class="back-link">Voltar para detalhes do cliente</a>
        <a href="adicionar_documento.php?id=<?php echo $cliente_id; ?>" class="add-document-link">Adicionar Novo Documento</a>
    </div>
</body>
</html>