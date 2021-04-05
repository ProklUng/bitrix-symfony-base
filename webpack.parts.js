const saveWebpackConfig = (filename, config) => {
  const fs = require("fs");

  fs.writeFileSync(filename, JSON.stringify(config));
};

const addBundleAnalyzerPlugin = (reportFilename, analyzerMode) => {
  const Encore = require("@symfony/webpack-encore");
  const { BundleAnalyzerPlugin } = require("webpack-bundle-analyzer");

  Encore.addPlugin(
      new BundleAnalyzerPlugin({
        reportFilename,
        analyzerMode,
      })
  );
};

const resolvePath = (...pathSegments) => {
  const path = require("path");
  const rootPath = process.cwd();

  return path.resolve(rootPath, ...pathSegments);
};

const configureDevServer = (assetsPath) => {
  const Encore = require("@symfony/webpack-encore");

  Encore
      //prettier-ignore
      .setManifestKeyPrefix(`${assetsPath}/`)

};

const prepareAliases = (aliases) => {
  for (let key in aliases) {
    aliases[key] = resolvePath(aliases[key]);
  }

  return aliases;
};

const addSVGSpritemapPlugin = (iconsPath, isProd) => {
  /**
   * Add SVG sprite
   *
   * Examole:
   *   <svg><use xlink:href="#sprite-burger"></use></svg>
   *
   * https://github.com/cascornelissen/svg-spritemap-webpack-plugin
   */
  const Encore = require("@symfony/webpack-encore");
  const SVGSpritemapPlugin = require("svg-spritemap-webpack-plugin");

  Encore.addPlugin(
      new SVGSpritemapPlugin(`${iconsPath}/**/*.svg`, {
        output: {
          // prettier-ignore
          filename: "images/icons.svg",
          svgo: isProd
              ? {
                plugins: [
                  {
                    convertColors: {
                      currentColor: true,
                    },
                    addAttributesToSVGElement: {
                      attributes: { fill: "currentColor" },
                    },
                  },
                ],
              }
              : false,
        },
      })
  );
};

const configureWatchOptions = () => {
  const Encore = require("@symfony/webpack-encore");

  Encore.configureWatchOptions((watchOptions) => {
    watchOptions.ignored = [
      "node_modules/**",
      "bitrix/**",
      "upload/**",
      "vendor/**",
      "sites/s1/**",
      "environments/**",
      "logs/**",
      "migrations/**",
      "local/classes/**",
      "local/components/**",
      "local/configs/**",
      "local/php_interface/**",
      "local/phpunit/**",
      "local/phpunit/**",
      "local/modules/**",
      "local/functions/**",
    ];
  });
};

const clean = () => {
  const Encore = require("@symfony/webpack-encore");

  //TODO: отключаем очистку в режиме dev-server, чтобы не снести прод или тп.
  Encore.cleanupOutputBeforeBuild(["**/*"]);
};

exports.configureDevServer = configureDevServer;
exports.resolvePath = resolvePath;
exports.addBundleAnalyzerPlugin = addBundleAnalyzerPlugin;
exports.saveWebpackConfig = saveWebpackConfig;
exports.prepareAliases = prepareAliases;
exports.addSVGSpritemapPlugin = addSVGSpritemapPlugin;
exports.configureWatchOptions = configureWatchOptions;
exports.clean = clean;
