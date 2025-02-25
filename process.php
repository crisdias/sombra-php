<?php
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'AmazonAPI.php';
require_once 'ImageProcessor.php';
require_once 'yourls.php';

use GuzzleHttp\Client;
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader);


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
        $productTitle   = $bookData['title'];

        // Processar a imagem (adicionar sombra)
        $processedImagePath = $imageProcessor->processImage($coverImagePath);

        // Gerar URL curta com código de afiliado
        $shortUrls  = $amazonAPI->generateShortAffiliateUrls($asin);
        if (defined('YOURLS_API_URL')) {
            $yourlsUrls = yourls_urls_array($shortUrls, $productTitle);
        } else {
            $yourlsUrls = null;
        }

        // Exibir a página de resultado
        displayResultPage($twig, $processedImagePath, $shortUrls, $productTitle, $yourlsUrls);
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

function displayResultPage($twig, $imagePath, $shortUrls, $title, $yourlsUrls = null)
{
    $imageUrl = 'img/' . basename($imagePath);

    echo $twig->render('result.twig', [
        'imageUrl' => $imageUrl,
        'shortUrls' => $shortUrls,
        'title' => $title,
        'yourlsUrls' => $yourlsUrls
    ]);
}






function sanitizeFilename($filename)
{
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
}
