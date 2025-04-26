<?php
include 'db_connect.php';
session_start();

if ($_SESSION['tipo'] !== 'dev' || $_SESSION['nivel'] < 10) {
    echo "<script>alert('Acesso negado.'); window.location.href='index.php';</script>";
    exit;
}

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM devs WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "<script>alert('Dev excluído com sucesso.'); window.location.href='lista_devs.php';</script>";
} else {
    echo "<script>alert('Erro ao excluir dev.'); window.location.href='lista_devs.php';</script>";
}
?>
