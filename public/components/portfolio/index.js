document.addEventListener('DOMContentLoaded', function () {
    // portfolio item click handler
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    const modal = new bootstrap.Modal(document.getElementById('portfolioModal'));

    portfolioItems.forEach(item => {
        item.addEventListener('click', function () {
            const title = this.dataset.title;
            const description = this.dataset.description;
            const date = this.dataset.date;
            const imageSrc = this.querySelector('img').src;

            document.getElementById('portfolioModalLabel').textContent = title;
            document.getElementById('portfolioModalDescription').textContent = description;
            document.getElementById('portfolioModalDate').textContent = date;
            document.getElementById('portfolioModalImage').src = imageSrc;

            modal.show();
        });
    });

    // filter functionality
    const filterBtns = document.querySelectorAll('.filter-btn');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const filter = this.dataset.filter;

            // update active button
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // filter portfolio items
            portfolioItems.forEach(item => {
                const category = item.dataset.category;

                if (filter === 'all' || category === filter) {
                    item.style.display = 'block';
                    item.style.animation = 'fadeInUp 0.6s ease forwards';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // add loading animation for images
    const images = document.querySelectorAll('.portfolio-item img');
    images.forEach(img => {
        img.addEventListener('load', function () {
            this.style.opacity = '1';
        });
    });
});