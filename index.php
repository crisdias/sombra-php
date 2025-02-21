<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Capas de Livros</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400&family=Montserrat:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <div class="form-container">
            <h1>Gerador de Capas</h1>
            <form action="process.php" method="GET">
                <input type="url" name="amazon_url" placeholder="Cole a URL da Amazon aqui" required>
                <button type="submit">Gerar Capa</button>
            </form>
        </div>
    </div>
</body>

</html>
