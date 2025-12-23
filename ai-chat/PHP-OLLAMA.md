# PHP-OLLAMA Integration Guide

This guide will help you integrate the AI Chat feature into your PHP e-commerce application using the provided Python backend.

---

## 📋 Prerequisites

Before you start, make sure you have:
- ✅ Python 3.7 or higher installed
- ✅ Ollama installed and running on your computer
- ✅ MySQL database with your e-commerce tables (`products`, `cart`, `users`)
- ✅ XAMPP or similar web server running
- ✅ A model downloaded in Ollama (e.g., `naruto`, `llama2`, `mistral`)

---

## Step 1: Set Up Python Environment

### 1.1 Navigate to Python Directory
Open your terminal/command prompt and navigate to the Python directory.

### 1.2 Create Virtual Environment
Create a virtual environment to isolate the project dependencies:

```bash
# On Windows (Git Bash)
python -m venv venv

# If that doesn't work, try:
python3 -m venv venv
```

### 1.3 Activate Virtual Environment
Activate the virtual environment:

```bash
# On Windows (Git Bash)
source venv/Scripts/activate

# On Windows (Command Prompt)
venv\Scripts\activate

# On Windows (PowerShell)
venv\Scripts\Activate.ps1
```

You should see `(venv)` in your terminal prompt when activated.

---

## Step 2: Install Required Packages

### 2.1 Install Dependencies
Install all required Python packages:

```bash
pip install -r requirements.txt
```

This will install:
- Flask (web framework)
- Flask-CORS (for cross-origin requests)
- mysql-connector-python (database connector)
- python-dotenv (environment variables)
- requests (HTTP library)

### 2.2 Verify Installation
Check if packages were installed correctly:

```bash
pip list
```

You should see all the required packages listed.

---

## Step 3: Configure Environment Variables

### 3.1 Create `.env` File
Create a new file named `.env` in the `ai-com/python/` directory.

**Option 1: Using Command Line**
```bash
cd d:\xampp\htdocs\APPDEV\ai-com\python
copy env.example .env
```

**Option 2: Manually**
- Open `env.example` file
- Copy its contents
- Create a new file named `.env` in the same directory
- Paste the contents

### 3.2 Configure Database Settings
Open the `.env` file and update these values:

```env
# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=          # Enter your MySQL password here (leave empty if no password)
DB_NAME=ecommerce_db  # Change to your actual database name

# Flask Server Configuration
APP_HOST=0.0.0.0
APP_PORT=5055

# Ollama Configuration
OLLAMA_HOST=127.0.0.1
OLLAMA_PORT=11434
OLLAMA_MODEL=naruto   # Change to match your installed Ollama model
OLLAMA_TIMEOUT=30
```

**Important:** 
- If your MySQL has no password, leave `DB_PASSWORD` empty
- Change `DB_NAME` to match your actual database name
- Change `OLLAMA_MODEL` to the model you have installed (check with `ollama list`)

---

## Step 4: Verify Ollama is Running

### 4.1 Check Installed Models
Open a new terminal and run:

```bash
ollama list
```

This shows all models you have installed. Make sure the model name in your `.env` file matches one of these.

### 4.2 Start Ollama (if not running)
If Ollama is not running, start it:

```bash
ollama serve
```

Keep this terminal open. Ollama must be running for the AI chat to work.

---

## Step 5: Start the Flask Server

### 5.1 Navigate to Python Directory
Make sure you're in the correct directory (Python directory).
```

### 5.2 Activate Virtual Environment (if not already active)
```bash
source venv/Scripts/activate
```

### 5.3 Run the Flask Application
Start the server:

```bash
python app.py
```

You should see output like:
```
Starting server on http://0.0.0.0:5055
 * Running on http://127.0.0.1:5055
```

**Important:** Keep this terminal window open! The Flask server must be running for the chat to work.

---

## Step 6: Test the Flask API

### 6.1 Test Health Endpoint
Open your browser and go to:
```
http://127.0.0.1:5055/health
```

You should see: `{"status":"ok"}`

### 6.2 Test Chat Endpoint (Optional)
You can test the chat API using a tool like Postman or curl, but this is optional. The main test will be through the web interface.

---

## Step 7: Integrate Chat Widget into PHP Pages

### 7.1 Choose Pages to Add Chat
Decide which pages should have the chat widget. Common choices:
- `index.php` (Homepage)
- `shop.php` (Product listing)
- `product.php` (Product details)
- `cart.php` (Shopping cart)

### 7.2 Add the Widget Code
Open any PHP file where you want the chat widget and add this code **just before the closing `</body>` tag**:

```php
<!-- AI Chat Widget -->
<script>
    // Configure API endpoint
    window.AI_COM_API = 'http://127.0.0.1:5055/ask';
    
    // Pass user context (if user is logged in)
    <?php if (isset($_SESSION['user_id'])): ?>
    window.AI_COM_CONTEXT = {
        userId: <?php echo (int)$_SESSION['user_id']; ?>
    };
    <?php else: ?>
    window.AI_COM_CONTEXT = {};
    <?php endif; ?>
</script>
<script src="ai-com/assets/ai-chat.js"></script>
```

### 7.3 Complete Example
Here's a complete example for `shop.php`:

```php
<?php
include './config/db.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop</title>
    <!-- Your existing CSS and head content -->
</head>
<body>
    <!-- Your existing page content -->
    <h1>Our Products</h1>
    <!-- ... rest of your content ... -->
    
    <!-- AI Chat Widget - Add before closing </body> -->
    <script>
        window.AI_COM_API = 'http://127.0.0.1:5055/ask';
        <?php if (isset($_SESSION['user_id'])): ?>
        window.AI_COM_CONTEXT = {
            userId: <?php echo (int)$_SESSION['user_id']; ?>
        };
        <?php else: ?>
        window.AI_COM_CONTEXT = {};
        <?php endif; ?>
    </script>
    <script src="ai-com/assets/ai-chat.js"></script>
</body>
</html>
```

**Note:** Make sure `session_start()` is called at the top of your PHP file if you're using `$_SESSION['user_id']`.

---

## Step 8: Test the Chat Widget

### 8.1 Open Your Website
1. Make sure XAMPP is running (Apache and MySQL)
2. Open your browser
3. Navigate to a page where you added the widget (e.g., `http://localhost/APPDEV/shop.php`)

### 8.2 Verify Widget Appearance
You should see a blue chat widget in the bottom-right corner with:
- Blue header saying "Shopping Assistant"
- A "Clear" button
- A message area
- An input field with placeholder: "Ask about products or your cart..."

### 8.3 Test Chat Functionality
Try these test messages:

1. **Basic Question:**
   - Type: "What products do you have?"
   - Click "Send" or press Enter
   - Wait for AI response

2. **Product Search:**
   - Type: "Show me running shoes"
   - The AI should search and show relevant products

3. **Cart Features (if logged in):**
   - Type: "Add product #1 to cart"
   - Type: "What's in my cart?"
   - Type: "Show me my cart"

---

## Step 9: Troubleshooting

### Problem: Chat widget doesn't appear
**Solutions:**
- Open browser console (Press F12) and check for JavaScript errors
- Verify the script path is correct: `ai-com/assets/ai-chat.js`
- Make sure the script is added before the closing `</body>` tag
- Check if the file `ai-com/assets/ai-chat.js` exists

### Problem: "Error contacting assistant"
**Solutions:**
- **Most Common:** Flask server is not running. Go to Step 5 and start it.
- Check if the API URL is correct: `http://127.0.0.1:5055/ask`
- Open `http://127.0.0.1:5055/health` in browser to verify server is running
- Check the Flask server terminal for error messages

### Problem: Database connection errors
**Solutions:**
- Verify database credentials in `.env` file
- Make sure MySQL is running in XAMPP
- Check if database name in `.env` matches your actual database
- Verify `DB_PASSWORD` is correct (or empty if no password)

### Problem: Ollama connection errors
**Solutions:**
- Make sure Ollama is running: `ollama serve`
- Check if model name in `.env` matches an installed model: `ollama list`
- Verify Ollama host/port in `.env` (default: `127.0.0.1:11434`)

### Problem: "Module not found" errors
**Solutions:**
- Make sure virtual environment is activated (you should see `(venv)` in terminal)
- Reinstall packages: `pip install -r requirements.txt`
- Verify you're in the correct directory: `cd d:\xampp\htdocs\APPDEV\ai-com\python`

### Problem: Port already in use
**Solutions:**
- Change `APP_PORT` in `.env` to a different port (e.g., `5056`)
- Update `window.AI_COM_API` in your PHP files to match the new port
- Or close the application using port 5055

---

## ✅ Quick Checklist

Before submitting your work, verify:

- [ ] Python virtual environment created and activated
- [ ] All packages installed (`pip install -r requirements.txt`)
- [ ] `.env` file created and configured with correct database credentials
- [ ] Ollama is running (`ollama serve`)
- [ ] Flask server is running (`python app.py`)
- [ ] Health endpoint works: `http://127.0.0.1:5055/health`
- [ ] Chat widget added to at least one PHP page
- [ ] Widget appears on the website
- [ ] Chat sends messages and receives responses
- [ ] Cart features work (if logged in)

---

## 📝 Important Notes

1. **Keep Flask Server Running:** The Flask server must be running whenever you want to use the chat feature. Keep the terminal window open.

2. **Keep Ollama Running:** Ollama must also be running. You can run it in a separate terminal window.

3. **Database Tables Required:** Make sure your database has these tables:
   - `products` (with columns: `id`, `name`, `description`, `price`, `quantity`, `is_active`)
   - `cart` (with columns: `user_id`, `product_id`, `quantity`)
   - `users` (with `id` column)

4. **Session Management:** The chat widget uses `$_SESSION['user_id']` to identify logged-in users. Make sure `session_start()` is called in your PHP files.

5. **File Paths:** The script path `ai-com/assets/ai-chat.js` is relative to your PHP file location. Adjust if your file structure is different.

---

## 🎯 What to Submit

When you're done, make sure you can demonstrate:
1. Flask server running successfully
2. Chat widget appearing on your PHP pages
3. Chat sending messages and receiving AI responses
4. Product search working through chat
5. Cart features working (if user is logged in)

---

## 🆘 Getting Help

If you encounter issues:
1. Check the Flask server terminal for Python errors
2. Check browser console (F12) for JavaScript errors
3. Verify all prerequisites are installed
4. Review the troubleshooting section above
5. Make sure both Flask server and Ollama are running

---

**Good luck with your implementation! 🚀**

