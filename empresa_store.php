<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['razao_social'];
    $cnpj = $_POST['cnpj'];
    $nome_fantasia = $_POST['nome_fantasia'];
    $endereco = $_POST['endereco'];

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
