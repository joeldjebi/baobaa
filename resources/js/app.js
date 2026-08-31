const loader = document.getElementById('baobaa-global-loader');
let loaderTimer = null;

const showLoader = () => {
    if (!loader) {
        return;
    }

    clearTimeout(loaderTimer);
    loaderTimer = setTimeout(() => {
        loader.classList.remove('hidden', 'opacity-0');
        loader.classList.add('flex', 'opacity-100');
        loader.setAttribute('aria-hidden', 'false');
    }, 140);
};

const hideLoader = () => {
    if (!loader) {
        return;
    }

    clearTimeout(loaderTimer);
    loader.classList.add('opacity-0');
    loader.classList.remove('opacity-100');
    loader.setAttribute('aria-hidden', 'true');

    setTimeout(() => {
        loader.classList.add('hidden');
        loader.classList.remove('flex');
    }, 180);
};

window.addEventListener('baobaa:loading-start', showLoader);
window.addEventListener('baobaa:loading-stop', hideLoader);
window.addEventListener('pageshow', hideLoader);

document.addEventListener('submit', (event) => {
    if (event.defaultPrevented || event.target.closest('[data-no-global-loader]')) {
        return;
    }

    showLoader();
});

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');

    if (!link || event.defaultPrevented || link.target || link.hasAttribute('download') || link.matches('[data-no-global-loader]')) {
        return;
    }

    const url = new URL(link.href, window.location.href);

    if (url.origin === window.location.origin && url.href !== window.location.href && !url.hash) {
        showLoader();
    }
});
