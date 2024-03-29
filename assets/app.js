/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

import('../node_modules/tw-elements/dist/js/tw-elements.umd.min.js');
import Like from './scripts/like';
console.log('Salut ! 🎉');

document.addEventListener('DOMContentLoaded', () => {
    const likeElements = document.querySelectorAll('a[data-action="like"]');
    if (likeElements.length > 0) {
        const likeArray = [].slice.call(likeElements);
        new Like(likeArray);
    }
})
