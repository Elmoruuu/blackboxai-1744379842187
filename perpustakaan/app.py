from flask import Flask, request, jsonify, send_from_directory, session, redirect, url_for
from flask_cors import CORS
import sqlite3
from datetime import datetime
import os
from werkzeug.security import generate_password_hash, check_password_hash

app = Flask(__name__, static_url_path='')
app.secret_key = 'your-secret-key-here'  # Change this to a secure secret key
CORS(app)

# Login required decorator
def login_required(f):
    def decorated_function(*args, **kwargs):
        if 'user_id' not in session:
            return jsonify({'error': 'Unauthorized'}), 401
        return f(*args, **kwargs)
    decorated_function.__name__ = f.__name__
    return decorated_function

# Route handlers
@app.route('/')
def root():
    return send_from_directory('.', 'index.html')

@app.route('/login')
def login_page():
    return send_from_directory('.', 'login.html')

@app.route('/api/login', methods=['POST'])
def login():
    data = request.json
    if not data or 'username' not in data or 'password' not in data:
        return jsonify({'error': 'Missing username or password'}), 400
    
    conn = sqlite3.connect('perpustakaan.db')
    c = conn.cursor()
    c.execute('SELECT * FROM users WHERE username = ?', (data['username'],))
    user = c.fetchone()
    conn.close()
    
    if user and check_password_hash(user[2], data['password']):
        session['user_id'] = user[0]
        session['username'] = user[1]
        session['role'] = user[3]
        return jsonify({'message': 'Login successful', 'role': user[3]})
    
    return jsonify({'error': 'Invalid username or password'}), 401

@app.route('/api/logout', methods=['POST'])
def logout():
    session.clear()
    return jsonify({'message': 'Logout successful'})

@app.route('/api/check-auth', methods=['GET'])
def check_auth():
    if 'user_id' in session:
        return jsonify({
            'authenticated': True,
            'username': session['username'],
            'role': session['role']
        })
    return jsonify({'authenticated': False})

# Database initialization
def init_db():
    conn = sqlite3.connect('perpustakaan.db')
    c = conn.cursor()
    
    # Create books table
    c.execute('''
        CREATE TABLE IF NOT EXISTS books (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            judul TEXT NOT NULL,
            penulis TEXT NOT NULL,
            kategori TEXT NOT NULL,
            isbn TEXT UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ''')
    
    # Create users table
    c.execute('''
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ''')
    
    # Create default admin user if not exists
    c.execute('SELECT * FROM users WHERE username = ?', ('admin',))
    if not c.fetchone():
        admin_password = generate_password_hash('admin123')
        c.execute('INSERT INTO users (username, password, role) VALUES (?, ?, ?)',
                 ('admin', admin_password, 'admin'))
    
    conn.commit()
    conn.close()

# Initialize database when app starts
init_db()

# Serve static files (index.html)
@app.route('/')
def home():
    return send_from_directory('.', 'index.html')

@app.route('/api/books', methods=['GET'])
def get_books():
    conn = sqlite3.connect('perpustakaan.db')
    c = conn.cursor()
    c.execute('SELECT * FROM books')
    books = c.fetchall()
    conn.close()
    
    return jsonify([{
        'id': book[0],
        'judul': book[1],
        'penulis': book[2],
        'kategori': book[3],
        'isbn': book[4],
        'created_at': book[5]
    } for book in books])

@app.route('/api/books', methods=['POST'])
@login_required
def add_book():
    data = request.json
    
    if not all(key in data for key in ['judul', 'penulis', 'kategori', 'isbn']):
        return jsonify({'error': 'Missing required fields'}), 400
    
    conn = sqlite3.connect('perpustakaan.db')
    c = conn.cursor()
    
    try:
        c.execute('''
            INSERT INTO books (judul, penulis, kategori, isbn)
            VALUES (?, ?, ?, ?)
        ''', (data['judul'], data['penulis'], data['kategori'], data['isbn']))
        conn.commit()
    except sqlite3.IntegrityError:
        conn.close()
        return jsonify({'error': 'ISBN already exists'}), 400
    
    book_id = c.lastrowid
    conn.close()
    
    return jsonify({
        'id': book_id,
        'message': 'Book added successfully'
    }), 201

@app.route('/api/books/<isbn>', methods=['DELETE'])
@login_required
def delete_book(isbn):
    conn = sqlite3.connect('perpustakaan.db')
    c = conn.cursor()
    
    c.execute('DELETE FROM books WHERE isbn = ?', (isbn,))
    conn.commit()
    
    if c.rowcount == 0:
        conn.close()
        return jsonify({'error': 'Book not found'}), 404
    
    conn.close()
    return jsonify({'message': 'Book deleted successfully'})

if __name__ == '__main__':
    app.run(debug=True, port=8000)
