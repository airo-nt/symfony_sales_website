/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// require jQuery normally
const $ = require('jquery');

// create global $ and jQuery variables
global.$ = global.jQuery = $;

import './styles/app.scss';

import 'bootstrap';
import Inputmask from 'inputmask';
import 'select2';
import './scripts/product_form';
import './scripts/products_list';

// start the Stimulus application
import './bootstrap';

$(document).ready(function() {
    Inputmask().mask($('.input-mask'));
    $('select.select2').select2();
});