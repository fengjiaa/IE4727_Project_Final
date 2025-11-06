document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('movies-container');
    const searchInput = document.getElementById('search-input');
    const genreFilter = document.getElementById('genre-filter');
    const dateFilter = document.getElementById('date-filter');
    const clearFilters = document.getElementById('clear-filters');

    let allMovies = [];
    let allShowtimes = {};

    // Load Movies + Build Filters
    fetch('api/movies.php')
        .then(res => res.json())
        .then(movies => {
            allMovies = movies;
            renderMovies(allMovies);

            // Populate genre filter
            const genres = [...new Set(movies.flatMap(m => m.genre.split(',').map(g => g.trim())))];
            genres.forEach(g => {
                const opt = document.createElement('option');
                opt.value = g;
                opt.textContent = g;
                genreFilter.appendChild(opt);
            });

            // Fetch all showtimes to populate date filter
            fetch('api/get_all_showtimes.php')
                .then(res => res.json())
                .then(showtimes => {
                    const uniqueDates = [...new Set(showtimes.map(s => s.show_date))];
                    uniqueDates.forEach(date => {
                        const opt = document.createElement('option');
                        opt.value = date;
                        opt.textContent = date;
                        dateFilter.appendChild(opt);
                    });

                    // Store showtimes by movie_id
                    showtimes.forEach(s => {
                        if (!allShowtimes[s.movie_id]) allShowtimes[s.movie_id] = [];
                        allShowtimes[s.movie_id].push(s);
                    });
                });
        })
        .catch(err => {
            console.error('Error fetching movies:', err);
            alert('Error loading movie list.');
        });

    // Render Movie Cards 
    function renderMovies(movies) {
        container.innerHTML = '';

        if (!movies.length) {
            container.innerHTML = '<p>No movies found.</p>';
            return;
        }

        movies.forEach(movie => {
            const card = document.createElement('article');
            card.className = 'card';
            card.innerHTML = `
                <!-- Poster -->
                <div class="col poster-col">
                    <img src="${movie.poster_url}" class="poster" alt="Poster for ${movie.movie_name}">
                </div>

                <!-- Movie Info -->
                <div class="col movie-info">
                    <h3>${movie.movie_name}</h3>
                    <p>${movie.description}</p>
                    <p><strong>Genre:</strong> ${movie.genre}</p>
                    <p><strong>Duration:</strong> ${movie.duration_mins} mins</p>
                </div>

                <!-- Showtime & Tickets -->
                <div class="col showtime-info">
                    <div class="showtime-form">
                        <label for="date-${movie.movie_id}">Date:</label>
                        <select id="date-${movie.movie_id}">
                            <option value="">Select Date</option>
                        </select>

                        <label for="showtime-${movie.movie_id}">Showtime:</label>
                        <select id="showtime-${movie.movie_id}">
                            <option value="">Select Showtime</option>
                        </select>

                        <label for="tickets-${movie.movie_id}">No. of Tickets:</label>
                        <input type="number" id="tickets-${movie.movie_id}" min="1" max="10" value="1">

                        <button class="btn btn-add-to-pref" onclick="addPref('${movie.movie_name}', ${movie.movie_id})">
                            Add to Preferences
                        </button>
                    </div>
                </div>
            `;

            container.appendChild(card);
            populateShowtimes(movie.movie_id);
        });
    }

    // Filter 
    function applyFilters() {
        const searchText = searchInput.value.toLowerCase();
        const selectedGenre = genreFilter.value;
        const selectedDate = dateFilter.value;

        const filtered = allMovies.filter(movie => {
            const matchesSearch =
                movie.movie_name.toLowerCase().includes(searchText) ||
                movie.description.toLowerCase().includes(searchText);
            const matchesGenre = !selectedGenre || movie.genre.includes(selectedGenre);
            const matchesDate =
                !selectedDate ||
                (allShowtimes[movie.movie_id] &&
                    allShowtimes[movie.movie_id].some(s => s.show_date === selectedDate));

            return matchesSearch && matchesGenre && matchesDate;
        });

        renderMovies(filtered);
    }

    searchInput.addEventListener('input', applyFilters);
    genreFilter.addEventListener('change', applyFilters);
    dateFilter.addEventListener('change', applyFilters);
    clearFilters.addEventListener('click', () => {
        searchInput.value = '';
        genreFilter.value = '';
        dateFilter.value = '';
        renderMovies(allMovies);
    });
});

// Populate Showtimes
function populateShowtimes(movie_id) {
    fetch(`api/get_showtimes.php?movie_id=${movie_id}`)
        .then(res => res.json())
        .then(showtimes => {
            const dateDropdown = document.getElementById(`date-${movie_id}`);
            const showtimeDropdown = document.getElementById(`showtime-${movie_id}`);

            dateDropdown.innerHTML = '<option value="">Select Date</option>';
            showtimeDropdown.innerHTML = '<option value="">Select Showtime</option>';

            if (!Array.isArray(showtimes) || !showtimes.length) return;

            const uniqueDates = [...new Set(showtimes.map(s => s.show_date))];
            uniqueDates.forEach(date => {
                const opt = document.createElement('option');
                opt.value = date;
                opt.textContent = date;
                dateDropdown.appendChild(opt);
            });

            dateDropdown.addEventListener('change', () => {
                const selectedDate = dateDropdown.value;
                const filtered = showtimes.filter(s => s.show_date === selectedDate);
                showtimeDropdown.innerHTML = '<option value="">Select Showtime</option>';
                filtered.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = JSON.stringify({ time: s.show_time, hall: s.hall });
                    opt.textContent = `${s.show_time} (${s.hall})`;
                    showtimeDropdown.appendChild(opt);
                });
            });
        });
}

// Add to Preferences
function addPref(title, movie_id) {
    const date = document.getElementById(`date-${movie_id}`).value;
    const showtimeData = document.getElementById(`showtime-${movie_id}`).value;
    const tickets = parseInt(document.getElementById(`tickets-${movie_id}`).value) || 1;

    if (!date || !showtimeData) {
        alert('Please select both date and showtime.');
        return;
    }

    const { time, hall } = JSON.parse(showtimeData);

    const prefs = JSON.parse(localStorage.getItem('prefs') || '[]');
    prefs.push({ title, movie_id, date, time, hall, pax: tickets });
    localStorage.setItem('prefs', JSON.stringify(prefs));

    alert(`Added to preferences (${tickets} ticket${tickets > 1 ? 's' : ''}). Redirecting to Booking...`);
    window.location.href = 'booking.html';
}
