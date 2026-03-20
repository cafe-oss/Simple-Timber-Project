@font-face {
  font-family: "GT America Expanded Bold";
  src: url("../assets/fonts/gt-america-expanded-bold.woff2") format("woff2");
  font-display: swap;
}

@font-face {
  font-family: "GT America Medium";
  src: url("../assets/fonts/gt-america-medium.woff2") format("woff2");
  font-display: swap;
}

font-display: swap; 

wp-content\themes\timber-jadev\views\html-header.twig
    <link rel="preload" href="{{ site.theme.link }}/assets/fonts/gt-america-expanded-bold.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ site.theme.link }}/assets/fonts/gt-america-medium.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="author" href="{{ site.theme.link }}/humans.txt" />