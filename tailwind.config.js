/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue", // <-- VERIFIQUE SE ESTA LINHA ESTÁ EXATAMENTE ASSIM
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
