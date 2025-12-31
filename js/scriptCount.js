// Counter Animation with Dynamic Updates
const CounterManager = {
  animateCounter(counter, newTarget = null) {
    // If newTarget is provided, update the data-target attribute
    if (newTarget !== null) {
      counter.parentElement.setAttribute("data-target", newTarget);
    }

    const target = +counter.parentElement.getAttribute("data-target");
    const duration = 2000; // total animation time in ms (2 seconds)
    const startTime = performance.now();

    const updateCount = currentTime => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);

      // Easing function (ease-out cubic)
      const easedProgress = 1 - Math.pow(1 - progress, 3);

      // Calculate current value
      const value = Math.floor(easedProgress * target);
      counter.textContent = value.toLocaleString(); // adds commas for readability

      if (progress < 1) {
        requestAnimationFrame(updateCount);
      } else {
        counter.textContent = target.toLocaleString();
      }
    };

    requestAnimationFrame(updateCount);
  },

  // Initialize counters on page load
  init() {
    const counters = document.querySelectorAll(".count");

    // Trigger animation when the counter becomes visible
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          this.animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.6 });

    counters.forEach(counter => observer.observe(counter));
  },

  // Update counter with new value (call this when filtering/changing connection type)
  updateCounter(counterElement, newValue) {
    this.animateCounter(counterElement, newValue);
  },

  // Update all counters based on filtered data
  updateAllCounters(projectCounts) {
    // projectCounts should be an object like: { general: 5, direct: 3, indirect: 8, audit: 2 }
    const counters = document.querySelectorAll(".count");
    
    counters.forEach(counter => {
      const card = counter.closest('.category-card');
      if (!card) return;

      // Determine which type this counter represents
      let type = null;
      if (card.classList.contains('general')) type = 'general';
      else if (card.classList.contains('direct')) type = 'direct';
      else if (card.classList.contains('indirect')) type = 'indirect';
      else if (card.classList.contains('audit')) type = 'audit';
      else if (card.classList.contains('manufacturing')) type = 'manufacturing';
      else if (card.classList.contains('administration')) type = 'administration';
      else if (card.classList.contains('wip')) type = 'wip';

      if (type && projectCounts.hasOwnProperty(type)) {
        this.updateCounter(counter, projectCounts[type]);
      }
    });
  }
};

// Initialize on page load
document.addEventListener("DOMContentLoaded", () => {
  CounterManager.init();
});

// Example usage when filtering projects:
// 
// Function to count projects by type based on current filter
function countProjectsByType(projects, filterType = null) {
  const counts = {
    general: 0,
    direct: 0,
    indirect: 0,
    audit: 0,
    manufacturing: 0,
    administration: 0,
    wip: 0
  };

  projects.forEach(project => {
    // Skip if doesn't match filter
    if (filterType && project.type !== filterType) return;

    const type = project.type.toLowerCase();
    if (counts.hasOwnProperty(type)) {
      counts[type]++;
    }
  });

  return counts;
}

// Example: Update counters when connection type changes
function onConnectionTypeChange(newType) {
  // Filter projects based on connection type
  const filteredProjects = allProjects.filter(p => {
    // Add your filtering logic here
    // For example: return p.connectionType === newType;
    return true; // placeholder
  });

  // Count projects by type
  const newCounts = countProjectsByType(filteredProjects);

  // Update all counters with new values
  CounterManager.updateAllCounters(newCounts);
}

// Alternative: If you have specific count values already
function updateCountersManually() {
  // Example: Update specific counter
  const generalCounter = document.querySelector('.category-card.general .count');
  if (generalCounter) {
    CounterManager.updateCounter(generalCounter, 15); // New count value
  }

  // Or update all at once
  CounterManager.updateAllCounters({
    general: 15,
    direct: 8,
    indirect: 12,
    audit: 5
  });
}