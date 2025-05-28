$(document).ready(function () {
    let currentIndex = 0;
    const items = $('.carousel-item');
    const itemAmt = items.length;
    const intervalTime = 8000; // 8 seconds

    function cycleItems() {
        items.removeClass('active');
        items.eq(currentIndex).addClass('active');
    }

    function nextItem() {
        currentIndex = (currentIndex + 1) % itemAmt;
        cycleItems();
    }

    function prevItem() {
        currentIndex = (currentIndex - 1 + itemAmt) % itemAmt;
        cycleItems();
    }

    // Auto Cycling
    let autoSlide = setInterval(nextItem, intervalTime);

    $('.carousel-control.next').click(function (e) {
        e.preventDefault();
        clearInterval(autoSlide);
        nextItem();
        autoSlide = setInterval(nextItem, intervalTime);
    });

    $('.carousel-control.prev').click(function (e) {
        e.preventDefault();
        clearInterval(autoSlide);
        prevItem();
        autoSlide = setInterval(nextItem, intervalTime);
    });
});