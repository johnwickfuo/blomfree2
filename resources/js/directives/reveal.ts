import type { Directive } from 'vue';

interface RevealEl extends HTMLElement {
    __revealObserver?: IntersectionObserver;
}

// Adds a fade-up animation the first time an element scrolls into view.
// Optional binding value sets a transition delay in milliseconds: v-reveal="120"
export const reveal: Directive<RevealEl, number | undefined> = {
    mounted(el, binding) {
        el.classList.add('reveal');

        if (typeof binding.value === 'number') {
            el.style.transitionDelay = `${binding.value}ms`;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        el.classList.add('reveal-visible');
                        observer.unobserve(el);
                    }
                });
            },
            { threshold: 0.15 },
        );

        observer.observe(el);
        el.__revealObserver = observer;
    },
    unmounted(el) {
        el.__revealObserver?.disconnect();
    },
};
