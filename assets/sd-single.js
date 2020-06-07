/**
 * JavaScript for single template of CPT sd_event
 * 
 * @package SeminardeskPlugin
 */

// get the modal and content
var modal = document.querySelector(".sd-modal");
// var contentMore = document.querySelector(".sd-modal-more");
// var contentBooking = document.querySelector(".sd-modal-booking");
// get buttons that open the modal
// var btnMore = document.querySelector(".sd-modal-more-btn");
var btnBooking = document.querySelector(".sd-modal-booking-btn");
// get button that closes the modal
var btnClose = document.querySelector(".sd-modal-close-btn");

// toggle between show and hide of the modal
function sdModalToggle() {
    modal.classList.toggle("sd-modal-show");
}

// show description content
// function sdModalShowMore() {
//     sdToggleModal();
//     contentBooking.style.display = "none";
//     contentMore.style.display = "block";
// }
// show booking content
// function sdModalShowBooking() {
//     sdToggleModal();
//     contentMore.style.display = "none";
//     contentBooking.style.display = "block";
// }

function windowOnClick(event) {
    if (event.target === modal) {
        sdModalToggle();
    }
}

// btnMore.addEventListener("click", sdModalShowMore);
btnBooking.addEventListener("click", sdModalToggle);
btnClose.addEventListener("click", sdModalToggle);
window.addEventListener("click", windowOnClick);

