/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./includes/**/*.php",
    "./views/**/*.html",
    "./admin-preview.html",
    "./assets/js/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        'wp-admin': {
          'text': '#1d2327',
          'text-light': '#50575e',
          'border': '#dcdcde',
          'border-light': '#8c8f94',
          'bg': '#f0f0f1',
          'card': '#ffffff',
          'input': '#f6f7f7',
          'primary': '#2271b1',
          'primary-hover': '#135e96',
          'success': '#00a32a',
          'success-bg': '#d7f0dc',
          'warning': '#dba617',
          'warning-bg': '#fcf3e6'
        }
      },
      fontFamily: {
        'admin': ['-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'Oxygen-Sans', 'Ubuntu', 'Cantarell', '"Helvetica Neue"', 'sans-serif'],
        'mono': ['"Monaco"', '"Menlo"', '"Ubuntu Mono"', '"Consolas"', 'monospace']
      },
      boxShadow: {
        'wp-card': '0 1px 3px rgba(0,0,0,0.04)'
      }
    },
  },
  plugins: [],
}
