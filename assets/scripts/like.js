import axios from "axios";

export default class like {
    constructor(likeElements) {
        this.likeElements = likeElements;

        if (this.likeElements) {
            this.init();
        }
    }

    init() {
        this.likeElements.forEach(el => {
            el.addEventListener('click', this.onClick.bind(this));
        });
    }


    onClick(event) {
        event.preventDefault();
        const currentElement = event.currentTarget;
        const url= currentElement.href;

        if (!url) {
            console.error('Href is not defined for the clicked element.');
            return;
        }
        axios.get(url).then(res => {
            const nb = res.data.nbLike;
            const span = currentElement.querySelector('span');
            currentElement.dataset.nb = nb;
            span.innerHTML = nb+' J\'aime';

            const thumbsUpFilled = currentElement.querySelector('svg.filled');
            const thumbsUpUnfilled = currentElement.querySelector('svg.unfilled');

            thumbsUpFilled.classList.toggle('hidden');
            thumbsUpUnfilled.classList.toggle('hidden');

        });
    }

}