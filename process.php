<?php
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'AmazonAPI.php';
require_once 'ImageProcessor.php';

use GuzzleHttp\Client;

// Definir o diretório de imagens
define('IMG_DIR', __DIR__ . '/img');

// Inicializar o processador de imagens
$imageProcessor = new ImageProcessor(IMG_DIR);

// Limpar imagens antigas
$imageProcessor->cleanOldImages();

if (($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') &&
    (isset($_POST['amazon_url']) || isset($_GET['amazon_url']))
) {

    $amazonUrl = isset($_POST['amazon_url']) ? $_POST['amazon_url'] : $_GET['amazon_url'];
    $amazonUrl = urldecode($amazonUrl); // Decodifica a URL

    try {
        // Extrair ASIN da URL
        $asin = extractAsin($amazonUrl);
        if (!$asin) {
            throw new Exception("ASIN não encontrado na URL fornecida: " . htmlspecialchars($amazonUrl));
        }

        // Obter dados do livro da Amazon
        $amazonAPI = new AmazonAPI();
        $bookData = $amazonAPI->getBookData($asin);

        // Baixar a imagem da capa
        $coverImagePath = downloadCoverImage($bookData['image_url']);

        // Processar a imagem (adicionar sombra)
        $processedImagePath = $imageProcessor->processImage($coverImagePath);

        // Gerar URL curta com código de afiliado
        $shortUrls = $amazonAPI->generateShortAffiliateUrls($asin);

        // Exibir a página de resultado
        displayResultPage($processedImagePath, $shortUrls, $bookData['title']);
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage();
    }
} else {
    echo "Método de requisição inválido ou URL da Amazon não fornecida.";
}

function extractAsin($url)
{
    // Padrão atualizado para lidar com URLs mais complexas
    if (preg_match('/\/dp\/([A-Z0-9]{10})/', $url, $matches)) {
        return $matches[1];
    }
    // Tenta outro padrão comum em URLs da Amazon
    if (preg_match('/\/([A-Z0-9]{10})\//', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

function downloadCoverImage($imageUrl)
{
    $client = new Client();
    $response = $client->get($imageUrl);
    $imageContent = $response->getBody()->getContents();

    $tempImagePath = tempnam(sys_get_temp_dir(), 'cover_') . '.jpg';
    file_put_contents($tempImagePath, $imageContent);

    return $tempImagePath;
}

function displayResultPage($imagePath, $shortUrls, $title)
{
    $imageUrl = 'img/' . basename($imagePath);

    // Gerar HTML das URLs curtas dentro de uma tabela
    $urlsHtml = '<table class="affiliate-table">';
    foreach ($shortUrls as $tag => $url) {
        $urlsHtml .= <<<HTML
        <tr>
            <td class="tag-name">{$tag}</td>
            <td><input type="text" value="{$url}" id="affiliateUrl-{$tag}" class="affiliate-url" readonly></td>
            <td><button onclick="copyToClipboard('affiliateUrl-{$tag}')" class="copy-button">
                <i class="fas fa-clipboard"></i>
            </button></td>
        </tr>
        HTML;
    }
    $urlsHtml .= '</table>';

    // HTML final
    $html = <<<HTML
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resultado - Gerador de Capas de Livros</title>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400&family=Montserrat:wght@500&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <style>
            .affiliate-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            .affiliate-table td {
                padding: 10px;
                border: 1px solid #ddd;
            }
            .tag-name {
                font-weight: bold;
                text-transform: capitalize;
                width: 15%;
            }
            .affiliate-url {
                width: 90%;
                padding: 5px;
                border: 1px solid #ccc;
                border-radius: 5px;
            }
            .copy-button {
                width: 40px;
                height: 40px;
                background-color: #007bff;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .copy-button i {
                font-size: 16px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="result-container">
                <img src="{$imageUrl}" alt="{$title}" class="result-image">
                <div class="affiliate-links">
                    {$urlsHtml}
                </div>
                <a href="index.php" class="button">Gerar Nova Capa</a>
            </div>
        </div>
        <script>
        function copyToClipboard(elementId) {
            var copyText = document.getElementById(elementId);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");

            var button = copyText.closest("tr").querySelector(".copy-button i");
            button.className = "fas fa-check";
            setTimeout(function() {
                button.className = "fas fa-clipboard";
            }, 2000);
        }
        </script>
    </body>
    </html>
    HTML;

    echo $html;
}


function sanitizeFilename($filename)
{
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
}
