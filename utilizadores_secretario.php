<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilizadores - Secretário</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<?php
include('functions.php');
$conn = dbConnection();
verificarSessaoSecretario();
terminarSessao();

$mensagem = '';

if (isset($_POST['adicionar_utilizador'])) {
    adicionarUtilizador($conn);
} elseif (isset($_POST['eliminar_utilizador'])) {
    eliminarUtilizador($conn);
}

function adicionarUtilizador($conn) {
    global $mensagem;

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $userType = $_POST['user_type'];

    $sql = "INSERT INTO utilizador (username_utilizador, email_utilizador, password_utilizador, fk_tipoutilizador, eliminado_utilizador) VALUES (?, ?, ?, ?, 0)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $username, $email, $password, $userType);

    if ($stmt->execute()) {
        $mensagem = "Utilizador Adicionado!";
    } else {
        $mensagem = "Erro ao inserir o utilizador: " . $stmt->error;
    }

    $stmt->close();
}

function eliminarUtilizador($conn) {
    global $mensagem;

    $idUtilizador = $_POST['id_utilizador'];

    $sql = "UPDATE utilizador SET eliminado_utilizador = 1 WHERE id_utilizador = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idUtilizador);

    if ($stmt->execute()) {
        $mensagem = "Utilizador Eliminado!";
    } else {
        $mensagem = "Erro ao eliminar o utilizador: " . $stmt->error;
    }

    $stmt->close();
}

function includeNavbar() {
    ?>
    <nav class='navbar navbar-expand-md navbar-light bg-light'>
        <div class='container-fluid'>
            <a class='navbar-brand'>
                <img src='src/logo.png' alt='Logo' height='40' class='d-inline-block align-text-top'>
            </a>
            <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav'
                aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation'>
                <span class='navbar-toggler-icon'></span>
            </button>
            <div class='collapse navbar-collapse' id='navbarNav'>
                <ul class='navbar-nav ms-auto'>
                    <li class='nav-item'>
                        <a class='nav-link' href='inicio_secretario.php' id='home-btn'>Página Inicial</a>
                    </li>
                    <li class='nav-item'>
                        <a class='nav-link' href='#' id='home-btn'>Utilizadores</a>
                    </li>
                    <li class='nav-item'>
                        <a class='nav-link' href='inicio_secretario.php?logout=true'>Terminar Sessão</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php
}

function mostrarTabela($conn, $sqlQuery, $tableTitle, $tableHeaders) {
    $stmt = $conn->prepare($sqlQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        ?>
        <div class='container my-5'>
            <div class='row justify-content-center'>
                <div class='col-md-8'>
                    <div class='card'>
                        <div class='card-header d-flex justify-content-between align-items-center'>
                            <span><b><?= $tableTitle ?></b></span>
                            <button class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#addUserModal'>Adicionar Utilizador</button>
                        </div>
                        <div class='card-body'>
                            <table class='table'>
                                <thead>
                                <tr>
                                    <?php foreach ($tableHeaders as $header) : ?>
                                        <th class='text-center'><?= $header ?></th>
                                    <?php endforeach; ?>
                                    <th class='text-center'>Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td class='text-center'><?= $row['username_utilizador'] ?></td>
                                        <td class='text-center'><?= $row['email_utilizador'] ?></td>
                                        <td class='text-center'><?= obterTipoUtilizador($conn, $row['fk_tipoutilizador']) ?></td>
                                        <td class='text-center'>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="id_utilizador" value="<?= $row['id_utilizador'] ?>">
                                                <button type="submit" class='btn btn-danger btn-sm' name="eliminar_utilizador">
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo "Erro na consulta: " . $stmt->error;
    }

    $stmt->close();
}

function obterTipoUtilizador($conn, $fk_tipoutilizador) {
    $stmt = $conn->prepare("SELECT titulo_tipo_utilizador FROM tipo_utilizador WHERE id_tipo_utilizador = ?");
    $stmt->bind_param("i", $fk_tipoutilizador);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        return $row['titulo_tipo_utilizador'];
    }

    return "Desconhecido";
}

includeNavbar();
mostrarTabela($conn, "SELECT id_utilizador, username_utilizador, email_utilizador, fk_tipoutilizador FROM utilizador WHERE eliminado_utilizador = 0", "Lista de Utilizadores", ["Nome de Utilizador", "E-mail", "Tipo de Utilizador"]);

$conn->close();
?>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addUserModalLabel">Adicionar Novo Utilizador</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <form method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Nome do Utilizador:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email do Utilizador:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password do Utilizador:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="user-type" class="form-label">Tipo de Utilizador:</label>
                <select class="form-select" id="user-type" name="user_type" required>
                    <option value="1">Aluno(a)</option>
                    <option value="2">Professor(a)</option>
                    <option value="3">Secretário(a)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" name="adicionar_utilizador">Adicionar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>