<?php
include('functions.php');

// Obter a conexão com o banco de dados
$conn = dbConnection();

session_start();

function redirect($location, $message = null) {
    if ($message) {
        $_SESSION['message'] = $message;
    }
    header("Location: $location");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obter os valores do formulário
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Consulta preparada para autenticação
    $sql = "SELECT id_utilizador, fk_tipoutilizador FROM Utilizador WHERE username_utilizador = ? AND password_utilizador = ? AND eliminado_utilizador = 0 LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Login bem-sucedido
        $user_data = $result->fetch_assoc();
        $_SESSION['id_utilizador'] = $user_data['id_utilizador'];
        $_SESSION['fk_tipoutilizador'] = $user_data['fk_tipoutilizador'];

        // Redirecionar com base no tipo de usuário
        switch ($_SESSION['fk_tipoutilizador']) {
            case 1:
                redirect("inicio_aluno.php");
                break;
            case 2:
                redirect("inicio_professor.php");
                break;
            case 3:
                redirect("inicio_secretario.php");
                break;
            default:
                redirect("index.php", 'Tipo de utilizador desconhecido.');
        }
    } else {
        // Login falhou
        redirect("index.php", 'Login falhou.');
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Login</title>
    <!-- Chamada do Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <img src="src/logo.png" alt="Logo" class="img-fluid mb-4 mx-auto d-block" style="max-height: 60px;">
                    <h2 class="mb-4 text-center">Página de Login</h2>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nome de Utilizador</label>
                            <input type="text" class="form-control" name="username" id="username" placeholder="Nome de Utilizador">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">Mostrar</button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" id="login">Iniciar Sessão</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        togglePasswordVisibility('password', 'togglePassword');
    });
</script>

<!-- Chamada do Ficheiro Scripts.js -->
<script src="scripts.js"></script>
<!-- Chamada do Bootstrap JS-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>
