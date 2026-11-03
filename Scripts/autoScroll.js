window.addEventListener("load", () => {

    const heroBar = document.getElementById("hero-bar");
    const items = [...heroBar.children];

    let current = 0;
    let direction = 1;
    let timer;
    
    const DELAY_LENGTH = 5000;
    const offset = heroBar.offsetLeft;
    
    // Prevent our own smooth scrolling from triggering the stop
    let autoScrolling = false;

    function scrollNext() {

        current += direction;

        if (current >= items.length - 1) {
            current = items.length - 1;
            direction = -1;
        } else if (current <= 0) {
            current = 0;
            direction = 1;
        }
        
        autoScrolling = true;

        heroBar.scrollTo({
            left: items[current].offsetLeft - offset,
            behavior: "smooth"
        });
        
        setTimeout(() => {
            autoScrolling = false;
        }, 800);
    }

    function startScrolling() {
        timer = setInterval(scrollNext, DELAY_LENGTH);
    }

    function stopScrolling() {
        clearInterval(timer);
    }
        
    if( items.length > 1 ) {
        startScrolling();

        /*
        heroBar.addEventListener("mouseenter", stopScrolling);
        heroBar.addEventListener("mouseleave", startScrolling);
        */
        
        // Stop if the user manually scrolls
        heroBar.addEventListener("scroll", () => {
        if (!autoScrolling) {
            stopScrolling();
        }
    });
    }
});

