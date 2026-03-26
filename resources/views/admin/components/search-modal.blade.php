<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <div class="input-group input-group-merge w-100">
          <span class="input-group-text border-0 bg-transparent">
            <i class="bx bx-search fs-4"></i>
          </span>
          <input
            type="text"
            class="form-control border-0 shadow-none py-3"
            id="searchInput"
            placeholder="Search..."
            autofocus
          >
          <button type="button" class="btn-close me-2" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body py-0">
        <!-- Recent Searches -->
        <div class="mb-5" id="recentSearchesSection">
          <h6 class="text-muted mb-3">Recent Searches</h6>
          <div class="d-flex flex-wrap gap-2" id="recentSearches">
            <!-- Recent searches will be populated here -->
          </div>
        </div>

        <!-- Popular Searches -->
        <div class="mb-5">
          <h6 class="text-muted mb-3">Popular Searches</h6>
          <div class="row g-3" id="popularSearches">
            <div class="col-sm-6 col-lg-4">
              <a href="#" class="d-flex align-items-center text-body p-2 rounded-3 hover-bg">
                <i class="bx bx-home me-3"></i>
                <span>Dashboard</span>
              </a>
            </div>
            <div class="col-sm-6 col-lg-4">
              <a href="#" class="d-flex align-items-center text-body p-2 rounded-3 hover-bg">
                <i class="bx bx-user me-3"></i>
                <span>Users</span>
              </a>
            </div>
            <div class="col-sm-6 col-lg-4">
              <a href="#" class="d-flex align-items-center text-body p-2 rounded-3 hover-bg">
                <i class="bx bx-cog me-3"></i>
                <span>Settings</span>
              </a>
            </div>
          </div>
        </div>

        <!-- Search Results -->
        <div id="searchResultsContainer" class="d-none">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="text-muted mb-0">Search Results</h6>
            <small class="text-muted" id="resultCount">0 results</small>
          </div>
          <div id="searchResults">
            <!-- Search results will be populated here -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
.hover-bg {
  transition: all 0.2s ease;
}

.hover-bg:hover {
  background-color: rgba(105, 108, 255, 0.1);
}

.modal {
  --bs-modal-width: 800px;
}

.modal-content {
  border: none;
  border-radius: 0.5rem;
  box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
}

.modal-header {
  padding: 1.5rem;
}

.modal-body {
  padding: 0 1.5rem 1.5rem;
  max-height: 60vh;
  overflow-y: auto;
}

#searchInput {
  font-size: 1.15rem;
}

#searchInput:focus {
  box-shadow: none;
}

.search-result-item {
  padding: 0.75rem 0;
  border-bottom: 1px solid #f5f5f5;
}

.search-result-item:last-child {
  border-bottom: none;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchModal = document.getElementById('searchModal');
  const searchInput = document.getElementById('searchInput');
  const searchResultsContainer = document.getElementById('searchResultsContainer');
  const recentSearchesSection = document.getElementById('recentSearchesSection');
  const resultCount = document.getElementById('resultCount');
  let searchTimeout;
  
  // Initialize recent searches from localStorage
  let recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
  
  // Update recent searches in the UI
  function updateRecentSearches() {
    const recentSearchesContainer = document.getElementById('recentSearches');
    if (!recentSearchesContainer) return;
    
    if (recentSearches.length === 0) {
      recentSearchesContainer.innerHTML = '<p class="text-muted">No recent searches</p>';
      return;
    }
    
    recentSearchesContainer.innerHTML = recentSearches
      .map(search => `
        <a href="#" class="badge bg-label-primary p-2" data-search="${search}">
          ${search}
          <i class="bx bx-x ms-1"></i>
        </a>
      `).join('');
  }
  
  // Save search to recent searches
  function saveToRecentSearches(query) {
    if (!query.trim()) return;
    
    // Remove if already exists
    const index = recentSearches.indexOf(query);
    if (index > -1) {
      recentSearches.splice(index, 1);
    }
    
    // Add to beginning
    recentSearches.unshift(query);
    
    // Keep only last 5 searches
    if (recentSearches.length > 5) {
      recentSearches.pop();
    }
    
    // Save to localStorage
    localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
    updateRecentSearches();
  }
  
  // Handle search
  function handleSearch(query) {
    if (!query.trim()) {
      searchResultsContainer.classList.add('d-none');
      recentSearchesSection.classList.remove('d-none');
      return;
    }
    
    // Show loading state
    searchResultsContainer.classList.remove('d-none');
    recentSearchesSection.classList.add('d-none');
    document.getElementById('searchResults').innerHTML = `
      <div class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Searching...</p>
      </div>
    `;
    
    // Clear previous timeout
    clearTimeout(searchTimeout);
    
    // Set new timeout
    searchTimeout = setTimeout(() => {
      // Create URL with query parameter
      const searchUrl = new URL('/admin/api/search', window.location.origin);
      searchUrl.searchParams.append('q', query);
      
      fetch(searchUrl, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        const resultsContainer = document.getElementById('searchResults');
        
        if (!data || data.length === 0) {
          resultsContainer.innerHTML = `
            <div class="text-center py-4">
              <i class="bx bx-search fs-1 text-muted mb-2"></i>
              <p class="text-muted">No results found for "${query}"</p>
            </div>
          `;
          resultCount.textContent = '0 results';
          return;
        }
        
        const html = data.map(item => `
          <a href="${item.url || '#'}" class="d-block search-result-item">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-3">
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-${item.color || 'primary'}">
                    <i class="${item.icon || 'bx bx-file'} fs-4"></i>
                  </span>
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">${item.title || 'Untitled'}</h6>
                ${item.description ? `<small class="text-muted">${item.description}</small>` : ''}
              </div>
              <div class="flex-shrink-0">
                <i class="bx bx-chevron-right text-muted"></i>
              </div>
            </div>
          </a>
        `).join('');
        
        resultsContainer.innerHTML = html;
        resultCount.textContent = `${data.length} ${data.length === 1 ? 'result' : 'results'}`;
        saveToRecentSearches(query);
      })
      .catch(error => {
        console.error('Search error:', error);
        document.getElementById('searchResults').innerHTML = `
          <div class="alert alert-danger">
            An error occurred while searching. Please try again.
            <div class="small mt-1">${error.message}</div>
          </div>
        `;
      });
    }, 300); // 300ms debounce
  }
  
  // Handle result click
  document.addEventListener('click', function(e) {
    const resultItem = e.target.closest('.search-result-item');
    if (resultItem) {
      e.preventDefault();
      const href = resultItem.getAttribute('href');
      if (href && href !== '#') {
        // Close the modal
        const modal = bootstrap.Modal.getInstance(searchModal);
        if (modal) {
          modal.hide();
        }
        // Navigate to the URL
        window.location.href = href;
      }
    }
  });
  
  // Event Listeners
  searchModal?.addEventListener('shown.bs.modal', () => {
    searchInput.focus();
    updateRecentSearches();
  });
  
  searchModal?.addEventListener('hidden.bs.modal', () => {
    searchInput.value = '';
    searchResultsContainer.classList.add('d-none');
    recentSearchesSection.classList.remove('d-none');
  });
  
  // Handle search input
  searchInput?.addEventListener('input', (e) => {
    handleSearch(e.target.value.trim());
  });
  
  // Handle form submission (prevent default)
  const searchForm = document.querySelector('#searchModal form');
  searchForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    handleSearch(searchInput.value.trim());
  });
  
  // Handle recent search clicks
  document.addEventListener('click', (e) => {
    // Recent search item click
    if (e.target.closest('[data-search]')) {
      e.preventDefault();
      const searchTerm = e.target.closest('[data-search]').getAttribute('data-search');
      searchInput.value = searchTerm;
      handleSearch(searchTerm);
    }
    
    // Close button in recent searches
    if (e.target.closest('.bx-x')) {
      e.preventDefault();
      e.stopPropagation();
      const searchTerm = e.target.closest('[data-search]').getAttribute('data-search');
      const index = recentSearches.indexOf(searchTerm);
      if (index > -1) {
        recentSearches.splice(index, 1);
        localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
        updateRecentSearches();
      }
    }
  });
  
  // Initialize recent searches
  updateRecentSearches();
  
  // Handle keyboard shortcuts
  document.addEventListener('keydown', (e) => {
    // Open search on Ctrl+K or Cmd+K
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      const modal = bootstrap.Modal.getInstance(searchModal);
      if (modal) {
        modal.show();
      } else {
        new bootstrap.Modal(searchModal).show();
      }
    }
  });
});
</script>
@endpush
