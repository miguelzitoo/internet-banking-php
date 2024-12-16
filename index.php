<?php
session_start();

// Usuario está logado?
if (!isset($_SESSION['cpf'])) {
    header("Location: Login.php"); // Redireciona para o login se não estiver logado
    exit;
}
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'banking';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $cpf = $_SESSION['cpf'];

    // Busca o nome do usuário e saldo
    $stmt = $pdo->prepare("SELECT usuario, saldo FROM login WHERE cpf = :cpf");
    $stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        $usuario = $resultado['usuario'];
        $saldo = $resultado['saldo'];
    } else {
        echo "Erro: Usuário não encontrado.";
        exit;
    }

    // Processa as operações
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = $_POST['acao'];
        $valor = floatval($_POST['valor']);
        $destinatario = $_POST['destinatario'] ?? null;

        if ($acao === 'depositar') {
            // Depósito
            $stmt = $pdo->prepare("UPDATE login SET saldo = saldo + :valor WHERE cpf = :cpf");
            $stmt->bindParam(':valor', $valor);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->execute();
            echo "<script>alert('Depósito de R$ $valor realizado com sucesso!');</script>";
        } elseif ($acao === 'sacar') {
            // Saque
            if ($saldo >= $valor) {
                $stmt = $pdo->prepare("UPDATE login SET saldo = saldo - :valor WHERE cpf = :cpf");
                $stmt->bindParam(':valor', $valor);
                $stmt->bindParam(':cpf', $cpf);
                $stmt->execute();
                echo "<script>alert('Saque de R$ $valor realizado com sucesso!');</script>";
            } else {
                echo "<script>alert('Saldo insuficiente para saque.');</script>";
            }
        } elseif ($acao === 'transferir' && $destinatario) {
            // Transferência
            $stmt = $pdo->prepare("SELECT saldo FROM login WHERE cpf = :destinatario");
            $stmt->bindParam(':destinatario', $destinatario);
            $stmt->execute();
            $destinatarioExiste = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($destinatarioExiste && $saldo >= $valor) {
                // Subtrair do remetente
                $stmt = $pdo->prepare("UPDATE login SET saldo = saldo - :valor WHERE cpf = :cpf");
                $stmt->bindParam(':valor', $valor);
                $stmt->bindParam(':cpf', $cpf);
                $stmt->execute();

                // Adicionar ao destinatário
                $stmt = $pdo->prepare("UPDATE login SET saldo = saldo + :valor WHERE cpf = :destinatario");
                $stmt->bindParam(':valor', $valor);
                $stmt->bindParam(':destinatario', $destinatario);
                $stmt->execute();

                echo "<script>alert('Transferência de R$ $valor para o CPF $destinatario realizada com sucesso!');</script>";
            } else {
                echo "<script>alert('Erro: CPF do destinatário inválido ou saldo insuficiente.');</script>";
            }
        }

        // Atualiza o saldo
        $stmt = $pdo->prepare("SELECT saldo FROM login WHERE cpf = :cpf");
        $stmt->bindParam(':cpf', $cpf);
        $stmt->execute();
        $saldo = $stmt->fetch(PDO::FETCH_COLUMN);
    }
} 
 catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rell="stylesheet" href="./css/carrossel.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <title>Internet Banking, seu banco de confiança</title>
</head>
<body>
    <ul>
        <li class="item"><a href="Logout.php" class="link" >Sair</a></li>
        <li><button onclick="openModal('depositar')" id="button-modal" class="button-li">Depositar</button></li>
        <li><button onclick="openModal('sacar')" id="button-modal" class="button-li">Sacar</button></li>
        <li><button onclick="openModal('transferir')" id="button-modal" class="button-li">Transferir</button></li>
        <li><i class="bi bi-eye"></i></li>
        <li class="item" id="saldo">R$ <?= number_format($saldo, 2, ',', '.')?></li>
        <li class="item1">Bem vindo <?php echo $usuario;?></li>
    </ul>
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <form method="POST">
                <h2 id="modal-title"></h2>
                <label for="valor" class="valor">Valor (R$) </label>
                <input type="number" step="0.01" id="valor" name="valor" class="valor-input"required><br><br>

                <div id="transfer-field" style="display: none;">
                    <label for="destinatario" class="destinatario">CPF do destinatário </label>
                    <input type="text" id="destinatario" name="destinatario" class="destinatario-input"><br><br>
                </div>

                <button type="submit" id="modal-action" name="acao" value="" class="confirm-button">Confirmar</button>
            </form>
        </div>
    </div>
    <img src="./icons/BANNER-1.jpg" alt="" class="imagem">
    <div class="box-pre">
        <h2 class="title">Deixe-nos simplificar o seu problema</h2>
    </div>
    <div class="container-container">
        <div class="container-box">
            <div class="box">
                <img src="./icons/boleto.png" alt="" class="box-img">
                <h3 class="box-title">Boleto</h3>
                <p class="box-texto">
                    Emita 2° via de boletos
                </p>
            </div>
            <div class="box">
                <img src="./icons/cartao.png" alt="" class="box-img">
                <h3 class="box-title">Desbloqueio de cartão</h3>
                <p class="box-texto">
                    Saiba como desbloquear o seu cartão VB
                </p>
            </div>
            <div class="box">
                <img src="./icons/comprovante.png" alt="" class="box-img">
                <h3 class="box-title">Comprovantes</h3>
                <p class="box-texto">
                    Gere uma 2° via do comprovante
                </p>
            </div>
            <div class="box">
                <img src="./icons/Suporte.png" alt="" class="box-img">
                <h3 class="box-title">Suporte</h3>
                <p class="box-texto">
                    Conheça nossos canais de ajuda
                </p>
            </div>
        </div>
    </div>

    <img src="./icons/BANNER2.jpg" alt="" class="imagem">


    <div class="box-pre">
        <h2 class="title">Quem sabe não podemos te ajudar</h2>
    </div>
    <div class="container-container">
        <div class="container-box">
            <div class="box-cc">
                <img src="./icons/VBbp.png" alt="" class="card-img">
                <div class="minibox">
                    <h3 class="box-title">Cartão VB Black Prestige</h3>
                    <p class="box-texto">
                        Tenha acesso a salas VIP e pontue até 1,5 por dólar
                    </p>
                    <p class="link-card"><a href="#" class="cartaolink">Peça já o seu cartão!</a></p>

                </div>

            </div>
            <div class="box-cc">
                <img src="./icons/VBg.png" alt="" class="card-img">
                <div class="minibox">
                    <h3 class="box-title">Cartão VB Gold</h3>
                    <p class="box-texto">
                        Tenha descontos em lojas parceiras e pontue até 0,5 por dólar
                    </p>
                    <p class="link-card"><a href="#" class="cartaolink">Peça já o seu cartão!</a></p>
                </div>

            </div>
            <div class="box-cc">
                <img src="./icons/VBc.png" alt="" class="card-img">
                <div class="minibox">
                    <h3 class="box-title">Cartão VB Classic</h3>
                    <p class="box-texto">
                    Tenha proteção dos seus dados e desconto em lojas parceiras
                    </p>
                    <p class="link-card"><a href="#" class="cartaolink">Peça já o seu cartão!</a></p>
                </div>

            </div>
        </div>
    </div>

    <div class="footer">
            <div class="agradecimentos">
                <h2 class="titulo">Este trabalho tem para fins academicos: o Seminário da faculdade de Analise e Desenvolvimento de Sistemas (ADS), feitos pelos respectivos alunos:</h2>
                <p class="alunos">Miguel Viggiano;</p>
                <p class="alunos">Lucas Elano Basso;</p>
                <p class="alunos">Danielle Milani Zanotto;</p>
                <p class="alunos">Ioleth Araujo Silva;</p>
                <p class="alunos">Alisson Castro Da Silva.</p>
                <br>
                <p class="alunos">Professor: Saulo Reschke Parizotto</p>
            </div>
    </div>


    <script>
        function openModal(action) {
            document.getElementById('modal').style.display = 'block';
            document.getElementById('modal-title').innerText = action.charAt(0).toUpperCase() + action.slice(1);
            document.getElementById('modal-action').value = action;

            if (action === 'transferir') {
                document.getElementById('transfer-field').style.display = 'block';
            } else {
                document.getElementById('transfer-field').style.display = 'none';
            }
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }
    </script>
</body>
</html>