from flask import Flask, request, jsonify, send_from_directory
from flask_cors import CORS
import sqlite3
from datetime import datetime
import os

app = Flask(__name__, static_url_path='')
CORS(app)

# Serve index.html at root
@app.route('/')
def root():
    return send_from_directory('.', 'index.html')

# Database initialization
def init_db():
    conn = sqlite3.connect('perpustakaan.db')
    c = conn.cursor()
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
