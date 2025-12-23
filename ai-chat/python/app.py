"""
Simple AI Shopping Assistant - Flask API
This file creates a web server that connects your PHP website to Ollama AI.
"""

from dotenv import load_dotenv
import os
import re
from flask import Flask, jsonify, request
from flask_cors import CORS
import mysql.connector
from ollama_client import OllamaClient

# -----------------------------
# Load .env
# -----------------------------
load_dotenv()  # Make sure .env is in ai-com/python/.env

# Test if variables are read
print("DB_USER:", os.getenv("DB_USER"))
print("DB_PASSWORD:", os.getenv("DB_PASSWORD"))
print("DB_HOST:", os.getenv("DB_HOST"))

# -----------------------------
# Create Flask app
# -----------------------------
app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})

# -----------------------------
# Create Ollama client
# -----------------------------
ollama_client = OllamaClient()

# -----------------------------
# Database connection
# -----------------------------
def get_db_connection():
    """Connect to MySQL database"""
    return mysql.connector.connect(
        host=os.getenv("DB_HOST", "localhost"),
        user=os.getenv("DB_USER", "root"),
        password=os.getenv("DB_PASSWORD", ""),  # empty password
        database=os.getenv("DB_NAME", "ecommerce_db")
    )

# -----------------------------
# Search products
# -----------------------------
def search_products(search_text):
    if not search_text:
        return []

    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    sql = """
        SELECT id, product_name AS name, description, price, stock
        FROM products
        WHERE status = 'active' 
        AND (product_name LIKE %s OR description LIKE %s)
        ORDER BY id DESC
        LIMIT 10
    """
    pattern = f"%{search_text}%"
    cursor.execute(sql, (pattern, pattern))
    products = cursor.fetchall()
    cursor.close()
    conn.close()
    return products

# -----------------------------
# Cart functions
# -----------------------------
def get_user_cart(user_id):
    if not user_id:
        return ""
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    sql = """
        SELECT c.product_id, c.quantity, p.product_name AS name, p.price
        FROM cart c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = %s
    """
    cursor.execute(sql, (user_id,))
    items = cursor.fetchall()
    cursor.close()
    conn.close()
    if not items:
        return ""
    cart_text = "Items in cart:\n"
    total = 0
    for item in items:
        price = float(item['price'])
        qty = int(item['quantity'])
        total += price * qty
        cart_text += f"- {item['name']} x{qty} — ₱{price:.2f}\n"
    cart_text += f"Total: ₱{total:.2f}"
    return cart_text

def add_to_cart(user_id, product_id, quantity=1):
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        "SELECT quantity FROM cart WHERE user_id = %s AND product_id = %s",
        (user_id, product_id)
    )
    existing = cursor.fetchone()
    if existing:
        new_qty = int(existing['quantity']) + quantity
        cursor.execute(
            "UPDATE cart SET quantity = %s WHERE user_id = %s AND product_id = %s",
            (new_qty, user_id, product_id)
        )
    else:
        cursor.execute(
            "INSERT INTO cart (user_id, product_id, quantity) VALUES (%s, %s, %s)",
            (user_id, product_id, quantity)
        )
    conn.commit()
    cursor.close()
    conn.close()
    return f"Added {quantity} item(s) to your cart!"

# -----------------------------
# Helper functions
# -----------------------------
def check_if_add_to_cart(message):
    message_lower = message.lower()
    return ("add" in message_lower and "cart" in message_lower) or \
           ("put" in message_lower and "cart" in message_lower)

def find_product_id_in_message(message, products):
    message_lower = message.lower()
    match = re.search(r'#(\d+)', message_lower)
    if match:
        product_id = int(match.group(1))
        for product in products:
            if product['id'] == product_id:
                return product
    for product in products:
        if product['name'].lower() in message_lower:
            return product
    return products[0] if products else None

# -----------------------------
# Routes
# -----------------------------
@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})

@app.route("/ask", methods=["POST"])
def ask():
    data = request.get_json()
    user_message = data.get("prompt", "").strip()
    user_id = data.get("user_id")

    if not user_message:
        return jsonify({"error": "No message provided"}), 400

    try:
        products = search_products(user_message)

        if not products:
            conn = get_db_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute("""
                SELECT id, product_name AS name, description, price, stock
                FROM products
                WHERE status = 'active'
                ORDER BY id DESC
                LIMIT 5
            """)
            products = cursor.fetchall()
            cursor.close()
            conn.close()

        wants_to_add = check_if_add_to_cart(user_message)

        if wants_to_add and products:
            if not user_id:
                return jsonify({"answer": "Please log in to add items to your cart."})
            product = find_product_id_in_message(user_message, products)
            if product:
                add_to_cart(user_id, product['id'], 1)
                return jsonify({
                    "answer": f"Added {product['name']} (₱{product['price']:.2f}) to your cart!"
                })
            else:
                product_list = ", ".join([f"#{p['id']} {p['name']}" for p in products[:5]])
                return jsonify({"answer": f"Which product? Here are some options: {product_list}"})

        cart_info = get_user_cart(user_id) if user_id else ""
        product_text = "Available products:\n"
        for product in products:
            product_text += f"#{product['id']} {product['name']} — ₱{product['price']:.2f} (Stock: {product['stock']})\n"
            product_text += f"Description: {product.get('description', 'No description')}\n\n"

        ai_prompt = product_text
        if cart_info:
            ai_prompt += f"\n{cart_info}\n"
        ai_prompt += f"\nUser question: {user_message}"

        system_message = (
            "You are a helpful shopping assistant. "
            "Answer questions about products using ONLY the information provided. "
            "Don't make up prices or product details. "
            "Be friendly and helpful. "
            "Currency is PHP (₱)."
        )

        messages = [
            {"role": "system", "content": system_message},
            {"role": "user", "content": ai_prompt}
        ]

        ai_response = ollama_client.chat(messages)
        answer = ai_response.get("message", {}).get("content", "Sorry, I couldn't understand that.")
        return jsonify({"answer": answer})

    except Exception as e:
        return jsonify({"error": str(e)}), 500

# -----------------------------
# Run app
# -----------------------------
if __name__ == "__main__":
    host = os.getenv("APP_HOST", "0.0.0.0")
    port = int(os.getenv("APP_PORT", "5055"))
    print(f"Starting server on http://{host}:{port}")
    app.run(host=host, port=port, debug=True)
