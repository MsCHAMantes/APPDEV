"""
Simple AI Shopping Assistant - Flask API
This file creates a web server that connects your PHP website to the database.
Only responds to specific predefined questions, but answers are fetched from DB.
"""

from dotenv import load_dotenv
import os
from flask import Flask, jsonify, request
from flask_cors import CORS
import mysql.connector

# -----------------------------
# Load .env
# -----------------------------
load_dotenv()

# -----------------------------
# Create Flask app
# -----------------------------
app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})

# -----------------------------
# Database connection
# -----------------------------
def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv("DB_HOST", "localhost"),
        user=os.getenv("DB_USER", "root"),
        password=os.getenv("DB_PASSWORD", ""),
        database=os.getenv("DB_NAME", "ecommerce_db")
    )

# -----------------------------
# Helper functions
# -----------------------------
def get_all_products():
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT name, price, stock FROM products WHERE status='active' AND is_active=1")
    products = cursor.fetchall()
    cursor.close()
    conn.close()
    return products

def get_products_by_category(category_name):
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    # Join products with categories to get category name
    sql = """
        SELECT p.name, p.price, p.stock
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.status='active' AND p.is_active=1 AND c.name=%s
    """
    cursor.execute(sql, (category_name,))
    products = cursor.fetchall()
    cursor.close()
    conn.close()
    return products

def get_product_by_name(product_name):
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT name, price, stock FROM products WHERE status='active' AND is_active=1 AND name=%s", (product_name,))
    product = cursor.fetchone()
    cursor.close()
    conn.close()
    return product

# -----------------------------
# Routes
# -----------------------------
@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})

@app.route("/ask", methods=["POST"])
def ask():
    data = request.get_json()
    user_message = data.get("prompt", "").strip().lower()

    if not user_message:
        return jsonify({"error": "No message provided"}), 400

    try:
        # Fixed trigger questions

        # Greeting
        if user_message == "hello!":
            return jsonify({"answer": "Hello! How can I assist you with our products today?"})

        # Farewell
        if user_message == "thanks!":
            return jsonify({"answer": "You're welcome! Have a great day 😊"})

        # List of product names
        if user_message == "list of the product available":
            products = get_all_products()
            names = [p['name'] for p in products]
            return jsonify({"answer": "Available products:\n- " + "\n- ".join(names)})

        # List products with price
        if user_message == "list of the product available with price":
            products = get_all_products()
            lines = [f"{p['name']} — ₱{float(p['price']):.2f}" for p in products]
            return jsonify({"answer": "Available products with price:\n- " + "\n- ".join(lines)})

        # Category-based questions
        if user_message == "lips product":
            products = get_products_by_category("Lipstick")
            names = [p['name'] for p in products]
            return jsonify({"answer": "Lipstick Products:\n- " + "\n- ".join(names)})

        if user_message == "eye product":
            products = get_products_by_category("Eye")
            names = [p['name'] for p in products]
            return jsonify({"answer": "Eye Products:\n- " + "\n- ".join(names)})

        if user_message == "foundation product":
            products = get_products_by_category("Foundation")
            names = [p['name'] for p in products]
            return jsonify({"answer": "Foundation Products:\n- " + "\n- ".join(names)})

        if user_message == "blush product":
            products = get_products_by_category("Blush")
            names = [p['name'] for p in products]
            return jsonify({"answer": "Blush Products:\n- " + "\n- ".join(names)})

        # Specific product details
        if user_message == "soft pinch liquid blush mini details":
            product = get_product_by_name("Soft Pinch Liquid Blush Mini")
            if product:
                return jsonify({"answer": f"Name: {product['name']}, Price: ₱{float(product['price']):.2f}, Stock: {product['stock']}"})
            return jsonify({"answer": "Product not found."})

        if user_message == "perfect strokes matte liquid liner":
            product = get_product_by_name("Perfect Strokes Matte Liquid Liner")
            if product:
                return jsonify({"answer": f"Name: {product['name']}, Price: ₱{float(product['price']):.2f}, Stock: {product['stock']}"})
            return jsonify({"answer": "Product not found."})

        return jsonify({"answer": "Sorry, I can only answer predefined product questions."})

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
