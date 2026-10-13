/**
 * BAOBAA motion system: scroll reveals, counters, rotating words, aurora parallax and card tilt.
 * Everything is skipped when the visitor prefers reduced motion.
 */
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const hasFinePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
const numberFormatter = new Intl.NumberFormat('fr-FR');

window.__baobaaMotion = true;

let revealObserver = null;

/**
 * Prepares staggered groups and observes every [data-reveal] element inside root.
 * Called again on content swapped in by AJAX (window.BaobaaMotion.refresh).
 */
const reveal = (root = document) => {
    root.querySelectorAll('[data-reveal-stagger]').forEach((group) => {
        const step = Number(group.dataset.revealStagger) || 80;

        [...group.children].forEach((child, index) => {
            child.dataset.reveal ??= group.dataset.revealVariant || '';
            child.style.setProperty('--reveal-delay', `${Math.min(index, 8) * step}ms`);
        });
    });

    const elements = root.querySelectorAll('[data-reveal]:not(.is-visible)');

    if (! ('IntersectionObserver' in window)) {
        elements.forEach((element) => element.classList.add('is-visible'));

        return;
    }

    revealObserver ??= new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (! entry.isIntersecting) {
                return;
            }

            const element = entry.target;

            element.classList.add('is-visible');
            element.dispatchEvent(new CustomEvent('baobaa:revealed'));
            revealObserver.unobserve(element);

            // Once revealed, hover transitions must not inherit the stagger delay.
            setTimeout(() => element.style.setProperty('--reveal-delay', '0ms'), 1400);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

    elements.forEach((element) => revealObserver.observe(element));
};

const animateCounter = (element) => {
    const target = Number(element.dataset.countTo || 0);
    const duration = 1400;
    const start = performance.now();

    const tick = (now) => {
        const progress = Math.min(1, (now - start) / duration);
        const eased = 1 - Math.pow(1 - progress, 3);

        element.textContent = numberFormatter.format(Math.round(target * eased));

        if (progress < 1) {
            requestAnimationFrame(tick);
        }
    };

    requestAnimationFrame(tick);
};

const counters = () => {
    const elements = document.querySelectorAll('[data-count-to]');

    if (! ('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.6 });

    elements.forEach((element) => {
        element.textContent = '0';
        observer.observe(element);
    });
};

const rotatingWords = () => {
    document.querySelectorAll('[data-rotating-words]').forEach((container) => {
        const words = [...container.children];

        if (words.length < 2) {
            return;
        }

        let index = 0;
        const fitWidth = () => {
            container.style.width = `${words[index].offsetWidth}px`;
        };

        words[0].classList.add('is-active');
        fitWidth();
        window.addEventListener('resize', fitWidth);
        document.fonts?.ready.then(fitWidth);

        setInterval(() => {
            if (document.hidden) {
                return;
            }

            const current = words[index];
            index = (index + 1) % words.length;
            const next = words[index];

            current.classList.remove('is-active');
            current.classList.add('is-leaving');
            next.classList.remove('is-leaving');
            next.classList.add('is-active');
            fitWidth();

            setTimeout(() => current.classList.remove('is-leaving'), 650);
        }, Number(container.dataset.rotatingWords) || 2600);
    });
};

const auroraParallax = () => {
    const layers = [...document.querySelectorAll('[data-aurora]')];

    if (! layers.length || ! hasFinePointer) {
        return;
    }

    let frame = null;

    window.addEventListener('pointermove', (event) => {
        if (frame) {
            return;
        }

        frame = requestAnimationFrame(() => {
            const x = event.clientX / window.innerWidth - 0.5;
            const y = event.clientY / window.innerHeight - 0.5;

            layers.forEach((layer) => {
                const depth = Number(layer.dataset.aurora) || 20;
                layer.style.transform = `translate3d(${x * depth}px, ${y * depth}px, 0)`;
            });

            frame = null;
        });
    }, { passive: true });
};

const tilt = () => {
    if (! hasFinePointer) {
        return;
    }

    document.addEventListener('pointermove', (event) => {
        const card = event.target.closest?.('[data-tilt]');

        document.querySelectorAll('[data-tilt].is-tilting').forEach((active) => {
            if (active !== card) {
                active.classList.remove('is-tilting');
                active.style.transform = '';
            }
        });

        if (! card) {
            return;
        }

        const rect = card.getBoundingClientRect();
        const x = (event.clientX - rect.left) / rect.width - 0.5;
        const y = (event.clientY - rect.top) / rect.height - 0.5;

        card.classList.add('is-tilting');
        card.style.transform = `perspective(900px) rotateX(${(-y * 6).toFixed(2)}deg) rotateY(${(x * 6).toFixed(2)}deg) translateY(-4px)`;
    }, { passive: true });

    document.addEventListener('pointerleave', () => {
        document.querySelectorAll('[data-tilt].is-tilting').forEach((card) => {
            card.classList.remove('is-tilting');
            card.style.transform = '';
        });
    });
};

const scrollProgress = () => {
    const bar = document.querySelector('[data-scroll-progress]');

    if (! bar) {
        return;
    }

    let frame = null;
    const update = () => {
        const max = document.documentElement.scrollHeight - window.innerHeight;
        bar.style.setProperty('--scroll-progress', max > 0 ? (window.scrollY / max).toFixed(4) : 0);
        frame = null;
    };

    window.addEventListener('scroll', () => {
        frame ??= requestAnimationFrame(update);
    }, { passive: true });
    update();
};

/**
 * Elements marked data-animate-on-view keep the "is-pending" class until they enter the viewport,
 * which lets CSS animate their children (chips, bubbles, calendar cells).
 */
const animateOnView = () => {
    const elements = document.querySelectorAll('[data-animate-on-view]');

    if (! ('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.remove('is-pending');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.35 });

    elements.forEach((element) => {
        element.classList.add('is-pending');
        observer.observe(element);
    });
};

/**
 * Scroll-told steps: the step closest to the middle of the screen drives the sticky phone screen and the timeline.
 */
const story = () => {
    document.querySelectorAll('[data-story]').forEach((root) => {
        const steps = [...root.querySelectorAll('[data-story-step]')];
        const screens = [...root.querySelectorAll('[data-story-screen]')];

        if (! steps.length) {
            return;
        }

        const activate = (index) => {
            steps.forEach((step, stepIndex) => step.classList.toggle('is-active', stepIndex === index));
            screens.forEach((screen, screenIndex) => screen.classList.toggle('is-active', screenIndex === index));
            root.style.setProperty('--story-progress', ((index + 1) / steps.length).toFixed(3));
            root.querySelectorAll('[data-story-dot]').forEach((dot, dotIndex) => dot.classList.toggle('is-reached', dotIndex <= index));
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    activate(steps.indexOf(entry.target));
                }
            });
        }, { rootMargin: '-45% 0px -45% 0px' });

        steps.forEach((step) => observer.observe(step));
        activate(0);
    });
};

/**
 * Public header: gets a frosted, bordered look once the page is scrolled.
 */
const headerScroll = () => {
    const header = document.querySelector('[data-site-header]');

    if (! header) {
        return;
    }

    const update = () => header.classList.toggle('is-scrolled', window.scrollY > 8);

    window.addEventListener('scroll', update, { passive: true });
    update();
};

/**
 * Main navigation: a pill slides under the hovered or focused link and returns to the current page.
 */
const navIndicator = () => {
    document.querySelectorAll('[data-nav]').forEach((nav) => {
        const indicator = nav.querySelector('[data-nav-indicator]');
        const links = [...nav.querySelectorAll('a')];

        if (! indicator || ! links.length) {
            return;
        }

        const currentLink = () => nav.querySelector('a[aria-current="page"]');
        const moveTo = (link) => {
            if (! link || ! link.offsetWidth) {
                indicator.style.setProperty('--nav-opacity', '0');

                return;
            }

            indicator.style.setProperty('--nav-x', `${link.offsetLeft}px`);
            indicator.style.setProperty('--nav-width', `${link.offsetWidth}px`);
            indicator.style.setProperty('--nav-opacity', '1');
        };

        nav.classList.add('has-indicator');
        moveTo(currentLink());
        requestAnimationFrame(() => nav.classList.add('is-ready'));

        links.forEach((link) => {
            link.addEventListener('pointerenter', () => moveTo(link));
            link.addEventListener('focus', () => moveTo(link));
        });
        nav.addEventListener('pointerleave', () => moveTo(currentLink()));
        nav.addEventListener('focusout', (event) => {
            if (! nav.contains(event.relatedTarget)) {
                moveTo(currentLink());
            }
        });
        window.addEventListener('resize', () => moveTo(currentLink()));
        document.fonts?.ready.then(() => moveTo(currentLink()));
    });
};

if (! prefersReducedMotion) {
    document.documentElement.classList.add('motion-ready');
    reveal();
    counters();
    rotatingWords();
    auroraParallax();
    tilt();
    scrollProgress();
    animateOnView();
    window.BaobaaMotion = { refresh: (root) => reveal(root) };
} else {
    document.documentElement.classList.remove('motion-ready');
}

story();
headerScroll();
navIndicator();
