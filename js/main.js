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

// Form Validation
function validateForm(formElement) {
    const inputs = formElement.querySelectorAll('input[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('border-red-500');
            
            // Create error message if it doesn't exist
            if (!input.nextElementSibling?.classList.contains('error-message')) {
                const errorMessage = document.createElement('p');
                errorMessage.className = 'text-red-500 text-sm mt-1 error-message';
                errorMessage.textContent = 'Field ini wajib diisi';
                input.parentNode.insertBefore(errorMessage, input.nextSibling);
            }
        } else {
            input.classList.remove('border-red-500');
            const errorMessage = input.nextElementSibling;
            if (errorMessage?.classList.contains('error-message')) {
                errorMessage.remove();
            }
        }
    });

    return isValid;
}

// Contact Form Handler
function initContactForm() {
    const contactForm = document.getElementById('contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            if (validateForm(contactForm)) {
                const formData = {
                    name: document.getElementById('name').value,
                    email: document.getElementById('email').value,
                    subject: document.getElementById('subject').value,
                    message: document.getElementById('message').value
                };
                
                // Here you would typically send this data to a server
                console.log('Form submitted:', formData);
                
                // Show success message
                alert('Terima kasih! Pesan Anda telah terkirim.');
                
                // Reset form
                contactForm.reset();
            }
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

// Event Calendar
function initEventCalendar() {
    const calendar = document.querySelector('.calendar');
    if (calendar) {
        const dates = calendar.querySelectorAll('.calendar-date');
        
        dates.forEach(date => {
            date.addEventListener('click', () => {
                // Remove selected class from all dates
                dates.forEach(d => d.classList.remove('selected'));
                // Add selected class to clicked date
                date.classList.add('selected');
                
                // Here you would typically fetch and display events for the selected date
                console.log('Selected date:', date.textContent);
            });
        });
    }
}

// Initialize all functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initContactForm();
    initBookSearch();
    initEventCalendar();
});

// Utility function to format dates
function formatDate(date) {
    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(date);
}

// Utility function to handle API errors
function handleError(error) {
    console.error('An error occurred:', error);
    // Here you would typically show a user-friendly error message
    alert('Maaf, terjadi kesalahan. Silakan coba lagi nanti.');
}
