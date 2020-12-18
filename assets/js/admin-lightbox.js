var slideIndex = 1;

const init_event_listeners = () => {
    url = window.location.href
    if(url.indexOf("koi-pricing") !== -1 && url.indexOf("koi-pricing-setting") === -1) {
        init_lightbox_events()
        init_dragndrop()
        init_accordion()
        init_pagination()
        init_refresh_filter()
        init_reorder_auction()
    }
}
const init_lightbox_events = () => {
    const prevButton = document.getElementsByClassName("lightbox-prev")[0]
    const nextButton = document.getElementsByClassName("lightbox-next")[0]
    prevButton.addEventListener("click", prevSlide)
    nextButton.addEventListener("click", nextSlide)

    const thumbnails = document.getElementsByClassName("koi-thumbnails")
    for(let i=0; i<thumbnails.length; i++) {
        thumbnails[i].addEventListener("click", init_lightbox)
    }
}
const init_lightbox = event => {
    const self = event.currentTarget
    const parentContainer = self.parentElement
    const thumbnails = parentContainer.getElementsByClassName("koi-thumbnails")
    var html = ''
    var demoHtml = ''
    for(let i=0; i<thumbnails.length; i++) {
        html += '<div class="koi-slides">'
        html += '<div class="numbertext">' + (i + 1) + ' / ' + thumbnails.length + '</div>'
        html += '<img src="' + thumbnails[i].dataset.thumb + '" style="height: 500px">'
        html += '</div>'

        demoHtml += '<div class="column">'
        demoHtml += '<img class="demo" src="' + thumbnails[i].dataset.thumb + '" onclick="currentSlide(' + (i + 1) + ')" alt="KOI-Image">'
        demoHtml += '</div>'
    }
    document.getElementById("koi-slides-container").innerHTML = html
    document.getElementById("lightbox-thumbnail-container").innerHTML = demoHtml
    currentSlide(self.dataset.index)
    openModal()
}
const openModal = () => {
    document.getElementById("lightbox-modal").style.display = "block";
}

const closeModal = () => { 
    document.getElementById("lightbox-modal").style.display = "none";
}

const nextSlide = () => {
    showSlides(slideIndex += 1);
}

const prevSlide = (n) => {
    showSlides(slideIndex -= 1);
}

const currentSlide = (n) => {
    showSlides(slideIndex = n);
}

const showSlides = (n) => {
    var i;
    var slides = document.getElementsByClassName("koi-slides");
    var dots = document.getElementsByClassName("demo");
    var captionText = document.getElementById("caption");
    if (n > slides.length) {slideIndex = 1}
    if (n < 1) {slideIndex = slides.length}
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    slides[slideIndex-1].style.display = "block";
    dots[slideIndex-1].className += " active";
    captionText.innerHTML = dots[slideIndex-1].alt;
}

jQuery(document).ready(function() {
    init_event_listeners()
})