/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/Views/**/*.php",
    "./app/Controllers/**/*.php",
    "./routes/**/*.php",
    "./public/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        "mazal-gray": "#F2F2F2",
        "mazal-forest": "#022601",
        "mazal-green": "#1F733C",
        "mazal-lime": "#2DD668",
        "mazal-orange": "#F29C50",
        "mazal-orange-dark": "#BF3617"
      },
      fontFamily: {
        sans: ["Nunito Sans", "Trebuchet MS", "Verdana", "sans-serif"],
        display: ["Barlow Condensed", "Arial Narrow", "sans-serif"]
      },
      boxShadow: {
        "cta-raised":
          "rgba(45,35,66,0.2) 0 2px 4px, rgba(45,35,66,0.15) 0 7px 13px -3px, #d6d6e7 0 -3px 0 inset"
      }
    }
  },
  plugins: []
};
