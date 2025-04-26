<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM exames WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // redireciona de volta para a lista
        header("Location: lista_exame.php?msg=Exame excluído com sucesso");
        exit();
    } else {
        echo "Erro ao excluir exame.";
    }
} else {
    echo "ID do exame não especificado.";
}
?>
