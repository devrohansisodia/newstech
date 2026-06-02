import { createApp } from 'vue';

/**
 * @param {string} selector
 * @param {import('vue').Component} component
 * @param {string} configSelector
 * @param {string} initializedFlag
 */
export function mountVueRoots(selector, component, configSelector, initializedFlag) {
    document.querySelectorAll(selector).forEach((element) => {
        if (! (element instanceof HTMLElement)) {
            return;
        }

        if (element.dataset[initializedFlag] === 'true') {
            element.dataset.vueMountStatus = 'already-initialized';

            return;
        }

        const configScript = element.querySelector(configSelector);
        const serializedConfig = configScript instanceof HTMLScriptElement
            ? configScript.textContent?.trim() ?? ''
            : '';

        if (serializedConfig === '') {
            element.dataset.vueMountStatus = 'missing-config';

            return;
        }

        let config;

        try {
            config = JSON.parse(serializedConfig);
        } catch {
            element.dataset.vueMountStatus = 'config-parse-failed';

            return;
        }

        try {
            const app = createApp(component, config);

            element.__vue_app__ = app;
            app.mount(element);
            element.dataset[initializedFlag] = 'true';
            element.dataset.vueMounted = 'true';
            element.dataset.vueMountStatus = 'mounted';
        } catch {
            element.dataset.vueMountStatus = 'mount-failed';
        }
    });
}
