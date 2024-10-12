javascript: (function () {
    var currentUrl = encodeURIComponent(window.location.href);
    var processorUrl = 'https://tools.crisdias.com/sombra/process.php?amazon_url=' + currentUrl;
    window.open(processorUrl, '_blank');
})();
