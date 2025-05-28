$(document).ready(function () {
    // when a portfolio item is clicked
    $('.portfolio-item').on('click', function () {
        // get data attributes from the clicked portfolio item
        const title = $(this).data('title');
        const description = $(this).data('description');
        const date = $(this).data('date');
        const imageSrc = $(this).find('img').attr('src'); // Get the image source

        // update the modal content
        $('#portfolioModalLabel').text(title);
        $('#portfolioModalDescription').text(description);
        $('#portfolioModalDate').text(date);
        $('#portfolioModalImage').attr('src', imageSrc);

        // show the modal
        $('#portfolioModal').modal('show');
    });
});