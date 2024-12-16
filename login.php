<?php
session_start();

// Configuração do banco de dados
$host = 'localhost';
$dbname = 'banking';
$user = 'root';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf'];
    $senha = $_POST['senha'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consulta para verificar login
        $stmt = $pdo->prepare("SELECT * FROM login WHERE cpf = :cpf AND senha = :senha");
        $stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
        $stmt->bindParam(':senha', $senha, PDO::PARAM_STR);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $_SESSION['cpf'] = $cpf; // Salva o CPF na sessão
            header("Location: Index.php"); // Redireciona para o Index.php
            exit;
        } else {
            $erro = "<script>alert('CPF ou senha inválida');</script>";
        }
    } catch (PDOException $e) {
        $erro = "Erro ao conectar ao banco de dados: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
  <title>Virutal Banking</title>
  <meta charset="utf-8">
  

  <meta name="author" content="Adtile">
  <meta name="viewport" content="width=device-width,initial-scale=1">
   
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="./css/mq1.css">
  <link rel="icon" type="image/x-icon" href="./icons/vbico.ico">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./css/login.css">
</head>
<body>
    <?php if (isset($erro)) echo "<p style='color: red;'>$erro</p>"; ?>
    <div class="login-page">
      <div class="login-div">
        <form method="POST" action="" class="login-form">
            <img src="./icons/banking.png" alt="Virtual Banking Logo" class="login-image">
            <input type="text" id="cpf" name="cpf" class="conta" placeholder="Conta" required><br><br>
            <div class="passwordDiv">
                <input type="password" id="senha" name="senha" class="senha" placeholder="Senha" required>
                <img src="./icons/eye.svg" alt="eye" class="eye" id="eye" onclick="mostrarSenha()">
            </div>
            <br><br>
            <button type="submit" class="button">Entrar</button>
            <p class="message">Ainda não tem conta? <a href="./php/registro.php" class="link">Clique aqui</a></p>
        </form>
      </div>
    </div>


    <script>
        function mostrarSenha() {
            var InputPass = document.getElementById('senha')
            var btnShowPass = document.getElementById('eye')

            if (InputPass.type === "password") {
                InputPass.setAttribute('type', 'text');
                btnShowPass.src='./icons/eye-slash.svg';
            } else {
                InputPass.setAttribute('type', 'password')
                btnShowPass.src='./icons/eye.svg'
            }
        }
    </script>
</body>
</html>


