/**
 * BAOBAA motion system: scroll reveals, counters, rotating words, aurora parallax and card tilt.
 * Everything is skipped when the visitor prefers reduced motion.
 */
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const hasFinePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
const numberFormatter = new Intl.NumberFormat('fr-FR');

window.__baobaaMotion = true;

const reveal = () => {
    document.querySelectorAll('[data-reveal-stagger]').forEach((group) => {
        const step = Number(group.dataset.revealStagger) || 80;

        [...group.children].forEach((child, index) => {
            child.dataset.reveal ??= group.dataset.revealVariant || '';
            child.style.setProperty('--reveal-delay', `${Math.min(index, 8) * step}ms`);
        });
    });

    const elements = document.querySelectorAll('[data-reveal]');

    if (! ('IntersectionObserver' in window)) {
        elements.forEach((element) => element.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (! entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-visible');
            entry.target.dispatchEvent(new CustomEvent('baobaa:revealed'));
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

    elements.forEach((element) => observer.observe(element));
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

if (! prefersReducedMotion) {
    document.documentElement.classList.add('motion-ready');
    reveal();
    counters();
    rotatingWords();
    auroraParallax();
    tilt();
} else {
    document.documentElement.classList.remove('motion-ready');
}
