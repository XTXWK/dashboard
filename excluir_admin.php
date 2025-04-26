<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Administrador excluído com sucesso.'); window.location.href='lista_admin.php';</script>";
    } else {
        echo "<script>alert('Erro ao excluir administrador.'); window.location.href='lista_admin.php';</script>";
    }
    $stmt->close();
} else {
    echo "<script>alert('ID inválido.'); window.location.href='lista_admin.php';</script>";
}
