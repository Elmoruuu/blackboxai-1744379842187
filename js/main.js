// Mobile Menu Toggle
function initMobileMenu() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

// Book Search and Filter
function initBookSearch() {
    const searchInput = document.getElementById('search');
    const categorySelect = document.getElementById('category');
    const availabilitySelect = document.getElementById('availability');
    const booksContainer = document.getElementById('books-container');

    if (searchInput && categorySelect && availabilitySelect && booksContainer) {
        const filterBooks = () => {
            const searchTerm = searchInput.value.toLowerCase();
            const category = categorySelect.value.toLowerCase();
            const availability = availabilitySelect.value.toLowerCase();
            
            // Get all book cards
            const bookCards = booksContainer.querySelectorAll('.book-card');
            
            bookCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const author = card.querySelector('p').textContent.toLowerCase();
                const bookCategory = card.dataset.category.toLowerCase();
                const isAvailable = card.dataset.available === 'true';
                
                // Check if the book matches all filters
                const matchesSearch = title.includes(searchTerm) || author.includes(searchTerm);
                const matchesCategory = !category || bookCategory === category;
                const matchesAvailability = !availability || 
                    (availability === 'tersedia' && isAvailable) || 
                    (availability === 'dipinjam' && !isAvailable);
                
                // Show/hide the card based on filters
                if (matchesSearch && matchesCategory && matchesAvailability) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        };

        // Add event listeners
        searchInput.addEventListener('input', filterBooks);
        categorySelect.addEventListener('change', filterBooks);
        availabilitySelect.addEventListener('change', filterBooks);
    }
}

// Add Book Modal
function initAddBookModal() {
    const modal = document.getElementById('add-book-modal');
    const openButton = document.getElementById('add-book-button');
    const closeButton = document.getElementById('close-modal');
    const cancelButton = document.getElementById('cancel-add-book');
    const form = document.getElementById('add-book-form');
    const booksContainer = document.getElementById('books-container');

    function showModal() {
        modal.classList.remove('hidden');
    }

    function hideModal() {
        modal.classList.add('hidden');
        form.reset();
    }

    function createBookCard(bookData) {
        const card = document.createElement('div');
        card.className = 'bg-white rounded-lg shadow-md overflow-hidden book-card';
        card.dataset.category = bookData.category;
        card.dataset.available = 'true';

        card.innerHTML = `
            <img src="${bookData.image}" alt="${bookData.title}" class="w-full h-48 object-cover">
            <div class="p-4">
                <span class="text-sm text-white bg-green-500 px-2 py-1 rounded-full">Tersedia</span>
                <h3 class="font-semibold text-lg mt-2">${bookData.title}</h3>
                <p class="text-gray-600 text-sm mb-2">Penulis: ${bookData.author}</p>
                <p class="text-gray-600 text-sm mb-4">Kategori: ${bookData.category}</p>
                <button class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                    Pinjam Buku
                </button>
            </div>
        `;

        return card;
    }

    if (openButton && closeButton && cancelButton && form && modal) {
        openButton.addEventListener('click', showModal);
        closeButton.addEventListener('click', hideModal);
        cancelButton.addEventListener('click', hideModal);

        form.addEventListener('submit', (e) => {
            e.preventDefault();

            const bookData = {
                title: document.getElementById('book-title').value,
                author: document.getElementById('book-author').value,
                category: document.getElementById('book-category').value,
                image: document.getElementById('book-image').value
            };

            const newBookCard = createBookCard(bookData);
            booksContainer.insertBefore(newBookCard, booksContainer.firstChild);

            hideModal();
            alert('Buku berhasil ditambahkan!');
        });

        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                hideModal();
            }
        });
    }
}

// Initialize all functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initBookSearch();
    initAddBookModal();
});
