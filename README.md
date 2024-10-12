# SombraLivro - Gerador de Capas de Livros da Amazon

SombraLivro é uma ferramenta web que gera imagens de capas de livros da Amazon com uma sombra aplicada, ideal para uso em newsletters, blogs e redes sociais. Este projeto é uma reimplementação em PHP de um script Python original, com assistência de IA para otimização e expansão das funcionalidades.

## Funcionalidades

- Gera imagens de capas de livros com sombra a partir de URLs da Amazon
- Cria URLs de afiliado curtas
- Interface web simples para entrada de URLs
- Bookmarklet para geração rápida de capas diretamente da página do produto da Amazon

## Requisitos

- PHP 7.4 ou superior
- Composer
- Extensões PHP: GD ou Imagick
- Acesso à API da Amazon Product Advertising
- Conta Bitly (para encurtamento de URLs)

## Instalação

1. Clone o repositório:
   ```
   git clone https://github.com/crisdias/sombraphp.git
   cd sombra
   ```

2. Instale as dependências via Composer:
   ```
   composer install
   ```

3. Copie `config-sample.php` para `config.php` e preencha com suas credenciais:
   ```
   cp config-sample.php config.php
   ```

4. Configure seu servidor web para servir o diretório do projeto.

## Uso

### Interface Web
1. Acesse a página inicial através do seu navegador.
2. Cole a URL do produto da Amazon na caixa de texto.
3. Clique em "Gerar Capa".

### Bookmarklet
1. Crie um novo favorito no seu navegador.
2. Como URL, cole o conteúdo do arquivo `bookmarklet.js`.
3. Ao visualizar um produto na Amazon, clique no bookmarklet para gerar a capa.

## Estrutura do Projeto

- `index.php`: Página inicial com formulário para entrada de URL
- `process.php`: Processa a URL e gera a imagem da capa
- `AmazonAPI.php`: Lida com as interações com a API da Amazon
- `ImageProcessor.php`: Processa e manipula as imagens
- `config.php`: Arquivo de configuração (não versionado)
- `style.css`: Estilos CSS para a interface
- `bookmarklet.js`: Código JavaScript para o bookmarklet

## Contribuindo

Contribuições são bem-vindas! Por favor, abra uma issue para discutir mudanças propostas ou envie um pull request.

## Licença

Este projeto está licenciado sob a [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.en.html).

## Agradecimentos

- Projeto original em Python por Cris Dias
- Assistência de desenvolvimento por IA (Claude da Anthropic)
