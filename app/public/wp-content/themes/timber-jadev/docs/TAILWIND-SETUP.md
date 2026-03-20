 ### Tailwind is not built into Timber - it was installed separately via npm. Looking at your project:   
 
 How it was installed:  // Your package.json shows:
  {                                                                                                                                                                                                             
    "dependencies": {
      "@tailwindcss/cli": "^4.1.11",
      "tailwindcss": "^4.1.11",
      "swiper": "^11.2.10"
    }
  }

  Someone (you or the theme author) ran:
  npm install tailwindcss @tailwindcss/cli

  Your Tailwind workflow:
  - Input: src/input.css (contains @import "tailwindcss" and @source directives)
  - Output: src/output.css (compiled CSS that your theme loads)

  The problem: You need to run the Tailwind CLI to compile CSS whenever you add new classes. To rebuild, run this from your theme folder:

  cd wp-content/themes/timber-jadev
  npx @tailwindcss/cli -i src/input.css -o src/output.css

  Or for development with auto-rebuild on file changes:
  npx @tailwindcss/cli -i src/input.css -o src/output.css --watch

  Summary:
  - Timber = Twig templating for WordPress (handles your .twig files)
  - Tailwind = CSS framework (installed separately via npm)
  - You must rebuild Tailwind CSS after adding new utility classes

  Try running the build command above and see if your add_class styles start working.