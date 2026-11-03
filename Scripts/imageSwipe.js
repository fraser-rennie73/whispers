//alert("here");

document.addEventListener('touchstart', handleTouchStart);
document.addEventListener('touchmove', handleTouchMove);
document.addEventListener('touchend', handleTouchEnd);

//const dest = document.getElementById("comment-text");
//dest.value = "script start";

var isSwipe = false;
var xDown = null;
var yDown = null;
var xUp = null;
var yDown = null;

let wholePage = null

/*
function getTouches(evt) {
  return evt.touches ||             // browser API
         evt.originalEvent.touches; // jQuery
}
*/

function reset() {
    isSwipe = false;
    xDown = null;
    yDown = null;
    xUp = null;
    yUp = null;
    
    wholePage = document.getElementById("image-page");
}

function handleTouchStart(evt) {
    /*
    const firstTouch = getTouches(evt)[0];
    xDown = firstTouch.clientX;
    dest.value += "\ntouch start";
    */
    reset();
    isSwipe = (evt.touches.length == 1);
    
    xDown = evt.touches[0].clientX;
    yDown = evt.touches[0].clientY;
    //alert("touch start - " + xDown);
}

function handleTouchMove(evt) {
    xUp = evt.touches[0].clientX;
    yUp = evt.touches[0].clientY;
    
    if( Math.abs(xDown - xUp) > Math.abs(yDown - yUp))
      wholePage.style.transform = "translateX(" + (xUp - xDown) + "px)";
}

function handleTouchEnd(evt) {
    if(!isSwipe || xUp === null)
        return;
    
    //const xUp = evt.touches[0].clientX;
    const xDiff = xDown - xUp;
    const yDiff = yDown - yUp;
    
    //wholePage.style.transform = "translateX(0px)";
    
    const w = window.screen.width;
    const pc = Math.abs(xDiff) / w;
    
    if( Math.abs(xDiff) > Math.abs(yDiff) && pc >= 0.33 ) {
    
    //alert("touch end - \n" + xDiff);
    if( xDiff > 0 ) {
        const link = document.getElementById( "next-image" );
        if ( link ) {
            link?.click();
            return;
        }
    }
    else if( xDiff < 0 ) {
        const link = document.getElementById( "prev-image" );
        if( link ) {
            link?.click();
            return;
        }
    }
    }
    
    wholePage.style.transform = "translateX(0px)";
    
    xDown = null;
    xUp = null;
}

/*
function handleTouchEnd(evt) {
    const firstTouch = getTouches(evt)[0];
    const xUp = firstTouch.clientX;
    
    dest.value += "\ntouch end";
    
    var xDiff = xDown - xUp;
    if ( xDiff > 0 ) {
        // right swipe
        dest.value += "\nright swipe";
        const link = document.getElementById( "prev-image" );
        if (link) link.click();
    } else if (xDiff < 0) {
        // left swipe
        dest.value += "\nleft swipe";
        const link = document.getElementById( "next-image" );
        if (link) link.click();
    }
}
*/


