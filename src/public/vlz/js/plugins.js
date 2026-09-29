/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Version: 3.0.0
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Common Plugins Js File
*/

// Common plugins
if (
  document.querySelector("[toast-list]") ||
  document.querySelector("[data-choices]") ||
  document.querySelector("[data-provider]")
) {
  let scripts = `
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script type="text/javascript" src="<?php echo base_url('vlz/libs/choices.js/public/assets/scripts/choices.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url('vlz/libs/flatpickr/flatpickr.min.js'); ?>"></script>
  `;
  document.body.insertAdjacentHTML("beforeend", scripts);
}
