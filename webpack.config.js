import Encore from '@symfony/webpack-encore';

Encore
    .setOutputPath('src/Resources/public/assets/')
    .addEntry('contao-watchlist-bundle', './src/Resources/assets/js/contao-watchlist-bundle.js')
    .setPublicPath('/public/assets/')
    .disableSingleRuntimeChunk()
    //.enableSassLoader()
    .configureBabel(function (babelConfig) {}, {
        // include to babel processing
        includeNodeModules: ['@hundh/contao-watchlist-bundle']
    })
    .enableSourceMaps(!Encore.isProduction())
;

export default Encore.getWebpackConfig();
