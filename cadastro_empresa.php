<?php
include 'db_connect.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$tipoUsuario = $_SESSION['tipo'];
$nivelDev = isset($_SESSION['nivel']) ? $_SESSION['nivel'] : null;

// Permitir apenas admin ou dev com nível 10
if (!(($tipoUsuario === 'dev' && $nivelDev == 10) || $tipoUsuario === 'admin')) {
    echo "<script>alert('Você não tem permissão para cadastrar empresas.'); window.location.href='index.php';</script>";
    exit;
}

// Se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['razao_social'];
    $cnpj = $_POST['cnpj'];
    $nome_fantasia = $_POST['nome_fantasia'];
    $endereco = $_POST['endereco'];

    // Inserção no banco
    $stmt = $conn->prepare("INSERT INTO empresas (nome, cnpj, nome_fantasia, endereco) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $cnpj, $nome_fantasia, $endereco);

    if ($stmt->execute()) {
        echo "<script>alert('Empresa cadastrada com sucesso!'); window.location.href='cadastrar_empresa.php';</script>";
    } else {
        echo "<script>alert('Erro ao cadastrar empresa.');</script>";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Empresa</title>
    <link rel="stylesheet" href="css/cadastro_empresa.css">
    <style>
      body {
        font-family: Arial, sans-serif;
        background-color: #f1f1f1;
        padding: 20px;
      }

      h2 {
        text-align: center;
        color: #333;
      }

      form {
        background-color: #fff;
        max-width: 500px;
        margin: auto;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
      }

      label {
        display: block;
        margin-top: 15px;
        font-weight: bold;
      }

      input[type="text"] {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-sizing: border-box;
      }

      button {
        margin-top: 20px;
        width: 100%;
        background-color: #007bff;
        color: white;
        padding: 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
      }

      button:hover {
        background-color: #0056b3;
      }
      .btn-dashboard {
        background-color: #6c757d;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 6px;
        font-size: 16px;
        display: inline-block;
    
      }
    </style>
</head>
<body>

<form id="formEmpresa" method="post" action="empresa_store.php">
    <label for="cnpj">CNPJ:</label>
    <input type="text" id="cnpj" name="cnpj" onblur="buscarCNPJ()" required>

    <label for="razao_social">Razão Social:</label>
    <input type="text" id="razao_social" name="razao_social" required>

    <label for="nome_fantasia">Nome Fantasia:</label>
    <input type="text" id="nome_fantasia" name="nome_fantasia">

    <label for="endereco">Endereço:</label>
    <input type="text" id="endereco" name="endereco">

    <button type="submit">Cadastrar</button>
    
    <?php
// Define o link de volta com base no tipo de usuário
$dashboardLink = ($tipoUsuario === 'admin') ? 'index.php' : 'dashboard_dev.php';
?>
<div style="text-align: center; margin-top: 20px;">
    <a href="<?php echo $dashboardLink; ?>" style="
        background-color: #6c757d;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 6px;
        font-size: 16px;
        display: inline-block;
    ">Voltar ao Dashboard</a>
</div>

</form>

<script>
async function buscarCNPJ() {
  const cnpjInput = document.getElementById('cnpj');
  const cnpj = cnpjInput.value.replace(/\D/g, '');

  if (cnpj.length !== 14) {
    alert('CNPJ inválido');
    return;
  }

  try {
    const response = await fetch(`consulta_cnpj.php?cnpj=${cnpj}`);
    const data = await response.json();

    if (data.status === 'ERROR') {
      alert(data.message || 'Erro ao consultar o CNPJ');
      return;
    }

    document.getElementById('razao_social').value = data.nome || '';
    document.getElementById('nome_fantasia').value = data.fantasia || '';
    document.getElementById('endereco').value = `${data.logradouro || ''}, ${data.numero || ''} - ${data.bairro || ''}, ${data.municipio || ''} - ${data.uf || ''}, ${data.cep || ''}`;
  } catch (error) {
    alert('Erro ao buscar CNPJ');
    console.error(error);
  }
}
</script>

</body>
</html>
