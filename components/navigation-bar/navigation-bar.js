document.addEventListener("DOMContentLoaded", function () {
    const OFFSET = 80; // adjust this based on your navbar height
    const serviceLinks = document.querySelectorAll(".service-link");

    serviceLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            e.preventDefault();

            const targetId = this.getAttribute("href").substring(1);
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
                const offsetPosition = elementPosition - OFFSET;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: "smooth"
                });
            }
        });
    });
});