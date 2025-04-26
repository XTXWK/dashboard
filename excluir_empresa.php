<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['empresa_id'])) {
    $empresa_id = $_POST['empresa_id'];

    // Verifica se há clientes vinculados a esta empresa
    $stmt = $conn->prepare("SELECT COUNT(*) FROM clientes WHERE empresa = (SELECT nome FROM empresas WHERE id = ?)");
    $stmt->bind_param("i", $empresa_id);
    $stmt->execute();
    $stmt->bind_result($qtd);
    $stmt->fetch();
    $stmt->close();

    if ($qtd > 0) {
        echo "<script>alert('Não é possível excluir a empresa porque há clientes vinculados a ela.'); window.location.href='lista_empresas.php';</script>";
        exit;
    }

    // Excluir a empresa
    $stmt = $conn->prepare("DELETE FROM empresas WHERE id = ?");
    $stmt->bind_param("i", $empresa_id);

    if ($stmt->execute()) {
        echo "<script>alert('Empresa excluída com sucesso.'); window.location.href='lista_empresas.php';</script>";
    } else {
        echo "<script>alert('Erro ao excluir empresa.'); window.location.href='lista_empresas.php';</script>";
    }

    $stmt->close();
} else {
    header("Location: lista_empresas.php");
    exit;
}
