import Encore from '@symfony/webpack-encore';

Encore
    .setOutputPath('public/assets/')
    .addEntry('contao-watchlist-bundle', './assets/js/contao-watchlist-bundle.js')
    .setPublicPath('/public/assets/')
    .disableSingleRuntimeChunk()
    .configureBabel(function (babelConfig) {
    }, {
        // include to babel processing
        includeNodeModules: ['@hundh/contao-watchlist-bundle']
    })
    .enableSourceMaps(!Encore.isProduction())
;

export default Encore.getWebpackConfig();
