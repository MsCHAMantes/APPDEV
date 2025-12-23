"""
Test script to verify AI Chat setup
Run this to check if everything is configured correctly
"""

import os
import sys
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

def test_imports():
    """Test if all required packages are installed"""
    print("🔍 Testing Python packages...")
    try:
        import flask
        print(f"  ✅ Flask {flask.__version__}")
    except ImportError:
        print("  ❌ Flask not installed. Run: pip install -r requirements.txt")
        return False
    
    try:
        import flask_cors
        print(f"  ✅ Flask-CORS installed")
    except ImportError:
        print("  ❌ Flask-CORS not installed. Run: pip install -r requirements.txt")
        return False
    
    try:
        import mysql.connector
        print(f"  ✅ mysql-connector-python installed")
    except ImportError:
        print("  ❌ mysql-connector-python not installed. Run: pip install -r requirements.txt")
        return False
    
    try:
        import requests
        print(f"  ✅ requests installed")
    except ImportError:
        print("  ❌ requests not installed. Run: pip install -r requirements.txt")
        return False
    
    try:
        import dotenv
        print(f"  ✅ python-dotenv installed")
    except ImportError:
        print("  ❌ python-dotenv not installed. Run: pip install -r requirements.txt")
        return False
    
    return True

def test_env_file():
    """Test if .env file exists and has required variables"""
    print("\n🔍 Testing .env file...")
    
    if not os.path.exists('.env'):
        print("  ❌ .env file not found!")
        print("  💡 Create it by copying env.example: cp env.example .env")
        return False
    
    print("  ✅ .env file exists")
    
    required_vars = ['DB_HOST', 'DB_USER', 'DB_NAME', 'OLLAMA_HOST', 'OLLAMA_PORT', 'OLLAMA_MODEL']
    missing = []
    
    for var in required_vars:
        value = os.getenv(var)
        if value:
            print(f"  ✅ {var} = {value}")
        else:
            print(f"  ⚠️  {var} not set (using default)")
            missing.append(var)
    
    if missing:
        print(f"  ⚠️  Some variables missing, but defaults may work")
    
    return True

def test_database():
    """Test database connection"""
    print("\n🔍 Testing database connection...")
    
    try:
        import mysql.connector
        
        conn = mysql.connector.connect(
            host=os.getenv("DB_HOST", "localhost"),
            user=os.getenv("DB_USER", "root"),
            password=os.getenv("DB_PASSWORD", ""),
            database=os.getenv("DB_NAME", "ecommerce_db")
        )
        
        cursor = conn.cursor()
        
        # Check if products table exists
        cursor.execute("SHOW TABLES LIKE 'products'")
        if cursor.fetchone():
            print("  ✅ Database connection successful")
            print("  ✅ 'products' table exists")
            
            # Check if cart table exists
            cursor.execute("SHOW TABLES LIKE 'cart'")
            if cursor.fetchone():
                print("  ✅ 'cart' table exists")
            else:
                print("  ⚠️  'cart' table not found (cart features won't work)")
        else:
            print("  ⚠️  'products' table not found")
        
        cursor.close()
        conn.close()
        return True
        
    except mysql.connector.Error as e:
        print(f"  ❌ Database connection failed: {e}")
        print("  💡 Check your .env file database credentials")
        return False
    except Exception as e:
        print(f"  ❌ Error: {e}")
        return False

def test_ollama():
    """Test Ollama connection"""
    print("\n🔍 Testing Ollama connection...")
    
    try:
        import requests
        
        host = os.getenv("OLLAMA_HOST", "127.0.0.1")
        port = os.getenv("OLLAMA_PORT", "11434")
        url = f"http://{host}:{port}/api/tags"
        
        response = requests.get(url, timeout=5)
        response.raise_for_status()
        
        models = response.json().get('models', [])
        model_names = [m.get('name', '') for m in models]
        
        print(f"  ✅ Ollama is running on {host}:{port}")
        print(f"  ✅ Found {len(model_names)} model(s): {', '.join(model_names) if model_names else 'None'}")
        
        # Check if configured model exists
        configured_model = os.getenv("OLLAMA_MODEL", "naruto")
        if any(configured_model in name for name in model_names):
            print(f"  ✅ Configured model '{configured_model}' is available")
        else:
            print(f"  ⚠️  Configured model '{configured_model}' not found in available models")
            print(f"  💡 Available models: {', '.join(model_names) if model_names else 'None'}")
            print(f"  💡 Update OLLAMA_MODEL in .env or run: ollama pull {configured_model}")
        
        return True
        
    except requests.exceptions.ConnectionError:
        print(f"  ❌ Cannot connect to Ollama at {host}:{port}")
        print("  💡 Make sure Ollama is running: ollama serve")
        return False
    except Exception as e:
        print(f"  ❌ Error connecting to Ollama: {e}")
        return False

def main():
    """Run all tests"""
    print("=" * 50)
    print("AI Chat Setup Verification")
    print("=" * 50)
    
    results = []
    
    results.append(("Python Packages", test_imports()))
    results.append(("Environment File", test_env_file()))
    results.append(("Database", test_database()))
    results.append(("Ollama", test_ollama()))
    
    print("\n" + "=" * 50)
    print("Test Results Summary")
    print("=" * 50)
    
    for name, passed in results:
        status = "✅ PASS" if passed else "❌ FAIL"
        print(f"{name:20} {status}")
    
    all_passed = all(result[1] for result in results)
    
    if all_passed:
        print("\n🎉 All tests passed! You're ready to start the server:")
        print("   python app.py")
    else:
        print("\n⚠️  Some tests failed. Please fix the issues above before starting the server.")
    
    return 0 if all_passed else 1

if __name__ == "__main__":
    sys.exit(main())

