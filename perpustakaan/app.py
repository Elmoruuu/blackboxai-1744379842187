from flask import Flask, request, jsonify, send_from_directory, session, redirect, url_for
from flask_cors import CORS
import sqlite3
import os
from werkzeug.security import generate_password_hash, check_password_hash
from functools import wraps
from datetime import datetime, timedelta

app = Flask(__name__, static_url_path='', static_folder='static')
app.secret_key = 'your-secret-key-here'
CORS(app)

# Database connection function
def get_db_connection():
    try:
        db_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'perpustakaan.db')
        conn = sqlite3.connect(db_path)
        conn.row_factory = sqlite3.Row
        return conn
    except sqlite3.Error as e:
        print(f"Error connecting to SQLite Database: {e}")
        return None

# Decorators
def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_id' not in session:
            return jsonify({'error': 'Unauthorized'}), 401
        return f(*args, **kwargs)
    return decorated_function

def admin_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_id' not in session or session.get('role') != 'admin':
            return jsonify({'error': 'Admin access required'}), 403
        return f(*args, **kwargs)
    return decorated_function

# Basic routes
@app.route('/')
def root():
    return send_from_directory('.', 'index.html')

@app.route('/login')
def login_page():
    return send_from_directory('.', 'login.html')

# Authentication routes
@app.route('/api/login', methods=['POST'])
def login():
    data = request.json
    if not data or 'username' not in data or 'password' not in data:
        return jsonify({'error': 'Missing username or password'}), 400
    
    conn = get_db_connection()
    if not conn:
        return jsonify({'error': 'Database connection error'}), 500
    
    cursor = conn.cursor()
    cursor.execute('SELECT * FROM users WHERE username = ?', (data['username'],))
    user = cursor.fetchone()
    cursor.close()
    conn.close()
    
    if user and user['password'] == data['password']:  # Direct comparison since we're storing the plain password for testing
        session['user_id'] = user['id']
        session['username'] = user['username']
        session['role'] = user['role']
        return jsonify({
            'message': 'Login successful',
            'role': user['role'],
            'username': user['username']
        })
    
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

# Book routes
@app.route('/api/books', methods=['GET'])
def get_books():
    level = request.args.get('level')
    category = request.args.get('category')
    search = request.args.get('search')
    
    conn = get_db_connection()
    if not conn:
        return jsonify({'error': 'Database connection error'}), 500
    
    cursor = conn.cursor()
    query = '''
        SELECT b.*, 
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(DISTINCT r.id) as review_count,
               COUNT(DISTINCT br.id) as times_borrowed
        FROM books b
        LEFT JOIN book_ratings r ON b.id = r.book_id
        LEFT JOIN borrowings br ON b.id = br.book_id
        WHERE 1=1
    '''
    params = []
    
    if level:
        query += ' AND b.education_level = ?'
        params.append(level)
    if category:
        query += ' AND b.category = ?'
        params.append(category)
    if search:
        query += ' AND (b.title LIKE ? OR b.author LIKE ? OR b.description LIKE ?)'
        search_param = f'%{search}%'
        params.extend([search_param, search_param, search_param])
    
    query += ' GROUP BY b.id'
    
    cursor.execute(query, params)
    books = [dict(row) for row in cursor.fetchall()]
    cursor.close()
    conn.close()
    
    return jsonify(books)

@app.route('/api/books/<int:book_id>', methods=['GET'])
def get_book_details(book_id):
    conn = get_db_connection()
    if not conn:
        return jsonify({'error': 'Database connection error'}), 500
    
    cursor = conn.cursor()
    
    # Get book details with ratings and reviews
    cursor.execute('''
        SELECT b.*, 
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(DISTINCT r.id) as review_count,
               COUNT(DISTINCT br.id) as times_borrowed
        FROM books b
        LEFT JOIN book_ratings r ON b.id = r.book_id
        LEFT JOIN borrowings br ON b.id = br.book_id
        WHERE b.id = ?
        GROUP BY b.id
    ''', (book_id,))
    result = cursor.fetchone()
    book = dict(result) if result else None
    
    if not book:
        cursor.close()
        conn.close()
        return jsonify({'error': 'Book not found'}), 404
    
    # Get reviews
    cursor.execute('''
        SELECT r.*, u.username
        FROM book_ratings r
        JOIN users u ON r.user_id = u.id
        WHERE r.book_id = ?
        ORDER BY r.created_at DESC
    ''', (book_id,))
    book['reviews'] = [dict(row) for row in cursor.fetchall()]
    
    cursor.close()
    conn.close()
    
    return jsonify(book)

@app.route('/api/books/<int:book_id>/borrow', methods=['POST'])
@login_required
def borrow_book(book_id):
    conn = get_db_connection()
    if not conn:
        return jsonify({'error': 'Database connection error'}), 500
    
    cursor = conn.cursor()
    
    # Check if book is available
    cursor.execute('SELECT stock FROM books WHERE id = ?', (book_id,))
    book = cursor.fetchone()
    
    if not book or book['stock'] <= 0:
        cursor.close()
        conn.close()
        return jsonify({'error': 'Book not available'}), 400
    
    # Check if user already has an active borrowing
    cursor.execute('''
        SELECT * FROM borrowings 
        WHERE user_id = ? AND book_id = ? AND status = 'borrowed'
    ''', (session['user_id'], book_id))
    
    if cursor.fetchone():
        cursor.close()
        conn.close()
        return jsonify({'error': 'You already have borrowed this book'}), 400
    
    # Create borrowing record
    due_date = datetime.now() + timedelta(days=14)
    cursor.execute('''
        INSERT INTO borrowings (user_id, book_id, due_date, borrowed_date)
        VALUES (?, ?, ?, datetime('now'))
    ''', (session['user_id'], book_id, due_date.strftime('%Y-%m-%d %H:%M:%S')))
    
    # Update book stock
    cursor.execute('''
        UPDATE books 
        SET stock = stock - 1, total_borrowed = total_borrowed + 1
        WHERE id = ?
    ''', (book_id,))
    
    conn.commit()
    cursor.close()
    conn.close()
    
    return jsonify({'message': 'Book borrowed successfully', 'due_date': due_date.isoformat()})

@app.route('/api/books/<int:book_id>/return', methods=['POST'])
@login_required
def return_book(book_id):
    conn = get_db_connection()
    if not conn:
        return jsonify({'error': 'Database connection error'}), 500
    
    cursor = conn.cursor()
    
    # Update borrowing record
    cursor.execute('''
        UPDATE borrowings 
        SET status = 'returned', returned_date = datetime('now')
        WHERE user_id = ? AND book_id = ? AND status = 'borrowed'
    ''', (session['user_id'], book_id))
    
    if cursor.rowcount == 0:
        cursor.close()
        conn.close()
        return jsonify({'error': 'No active borrowing found'}), 400
    
    # Update book stock
    cursor.execute('''
        UPDATE books 
        SET stock = stock + 1
        WHERE id = ?
    ''', (book_id,))
    
    conn.commit()
    cursor.close()
    conn.close()
    
    return jsonify({'message': 'Book returned successfully'})

@app.route('/api/books/<int:book_id>/rate', methods=['POST'])
@login_required
def rate_book(book_id):
    data = request.json
    if not data or 'rating' not in data:
        return jsonify({'error': 'Rating is required'}), 400
    
    rating = data['rating']
    if not isinstance(rating, int) or rating < 1 or rating > 5:
        return jsonify({'error': 'Rating must be between 1 and 5'}), 400
    
    conn = get_db_connection()
    if not conn:
        return jsonify({'error': 'Database connection error'}), 500
    
    cursor = conn.cursor()
    try:
        # SQLite doesn't have ON DUPLICATE KEY UPDATE, so we need to use INSERT OR REPLACE
        cursor.execute('''
            INSERT OR REPLACE INTO book_ratings (book_id, user_id, rating, review)
            VALUES (?, ?, ?, ?)
        ''', (book_id, session['user_id'], rating, data.get('review', '')))
        conn.commit()
        
        # Update book's average rating
        cursor.execute('''
            UPDATE books b
            SET rating = (
                SELECT AVG(rating)
                FROM book_ratings
                WHERE book_id = ?
            )
            WHERE id = ?
        ''', (book_id, book_id))
        conn.commit()
        
        cursor.close()
        conn.close()
        return jsonify({'message': 'Rating submitted successfully'})
    except sqlite3.Error as e:
        return jsonify({'error': str(e)}), 400

@app.route('/api/user/history', methods=['GET'])
@login_required
def get_user_history():
    conn = get_db_connection()
    if not conn:
        return jsonify({'error': 'Database connection error'}), 500
    
    cursor = conn.cursor()
    cursor.execute('''
        SELECT b.*, br.borrowed_date, br.due_date, br.returned_date, br.status,
               r.rating, r.review
        FROM borrowings br
        JOIN books b ON br.book_id = b.id
        LEFT JOIN book_ratings r ON b.id = r.book_id AND r.user_id = br.user_id
        WHERE br.user_id = ?
        ORDER BY br.borrowed_date DESC
    ''', (session['user_id'],))
    
    history = [dict(row) for row in cursor.fetchall()]
    cursor.close()
    conn.close()
    
    return jsonify(history)

if __name__ == '__main__':
    app.run(debug=True, port=8000)
