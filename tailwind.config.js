/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.{html,php}",
    "./api/*.{html,php}",
  ],
  theme: {
    extend: {
       colors: {
           'jru-blue': '#1e3a8a',
           'jru-navy': '#1e40af',
           'jru-gold': '#f59e0b',
       }
    },
  },
  plugins: [],
}
