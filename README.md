# PHP Internship - Task 1: Environment Setup

This is the repository structure for **Task 1** of my PHP Developer Internship. It establishes a basic, clean, and responsive web interface to verify that the local PHP development environment and client-side scripts are working in harmony.

---

## Folder Organization

```text
blog-project/
├── index.php         # Entry point: renders page layout and server configurations
├── README.md         # Documentation: project details and execution guide
├── css/
│   └── style.css     # Styling: clean, layout flexbox configurations, and card interfaces
├── js/
│   └── script.js     # Interactivity: DOM listeners and welcome verification handlers
└── images/           # Assets: stores graphical banners and site icons
```

---

## File Explanations

1. **`index.php`**: Standard procedural PHP page displaying welcome notes and dynamically reading local server information (such as PHP version, Host, OS, and remote browser details) using standard `$_SERVER` and `phpversion()` functions.
2. **`css/style.css`**: Features a clean stylesheet with custom responsive elements, resetting standard browser defaults and implementing cards that shift layout on smaller viewports.
3. **`js/script.js`**: Integrates interactive events on buttons using modern `addEventListener('click')` calls. It logs messages to the developer console and manipulates DOM values dynamically to prove front-end scripts work.
4. **`README.md`**: Provides instruction manuals for the workspace configuration.

---

## How to Run This Project

Since **XAMPP** is installed on this system, you can run the site using either PHP's built-in quick server or through XAMPP's server directory.

### Method A: Running via PHP Built-in Server (Recommended for quick testing)
This lets you launch the server directly from this folder without moving any files.

1. Open a terminal (PowerShell/Command Prompt) in this project folder (`c:\Users\Abhinav\OneDrive\Desktop\Blog`).
2. Run this command:
   ```bash
   C:\xampp\php\php.exe -S localhost:8000
   ```
3. Open your browser and go to:
   ```text
   http://localhost:8000
   ```

### Method B: Hosting inside XAMPP htdocs
If you want to host it permanently through XAMPP's Apache service:

1. Copy the folder containing these files.
2. Go to your local XAMPP installation directory: `C:\xampp\htdocs\`
3. Create a folder named `blog-project` and paste the files inside.
4. Open the **XAMPP Control Panel** and click **Start** next to **Apache**.
5. Open your browser and navigate to:
   ```text
   http://localhost/blog-project
   ```
