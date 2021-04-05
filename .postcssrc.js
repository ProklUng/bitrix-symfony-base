const defaultPostcssPixelToViewportConfig = {
  viewportUnit: "vw",
  propertyBlacklist: [/(.*)/],
  minPixelValue: 2,
  mediaQuery: false,
};

module.exports = ({ file, options, env }) => ({
  syntax: "postcss-scss",
  plugins: [
    require("postcss-import")({ root: file.dirname }),
    require("postcss-pixel-to-viewport")({
      ...defaultPostcssPixelToViewportConfig,

      viewportWidth: 320,
      enableConvertComment: `xs-viewport`,
      disableConvertComment: `xs-viewport-off`,
    }),
    require("postcss-pixel-to-viewport")({
      ...defaultPostcssPixelToViewportConfig,

      viewportWidth: 768,
      enableConvertComment: `md-viewport`,
      disableConvertComment: `md-viewport-off`,
    }),
    require("postcss-pixel-to-viewport")({
      ...defaultPostcssPixelToViewportConfig,

      viewportWidth: 1024,
      enableConvertComment: `lg-viewport`,
      disableConvertComment: `lg-viewport-off`,
    }),
    require("postcss-pixel-to-viewport")({
      ...defaultPostcssPixelToViewportConfig,

      viewportWidth: 1920,
      enableConvertComment: `xl-viewport`,
      disableConvertComment: `xl-viewport-off`,
    }),
    require("postcss-responsive-font")(),
    require("postcss-calc")(),
    require("postcss-flexbugs-fixes")(),
    require("postcss-preset-env")({
      autoprefixer: {
        grid: env === "production",
      },
    }),
  ],
});