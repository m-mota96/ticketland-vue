import '../css/app.css';
import './bootstrap';
import 'element-plus/dist/index.css';
import 'bulma/css/bulma.css';
import '../css/generalStyles.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import ElementPlus from 'element-plus';
import VueTheMask from 'vue-the-mask';
import es from 'element-plus/dist/locale/es.mjs';
es.el.pagination.pagesize = ' por página'; // Cambia el texto aquí
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { fab } from '@fortawesome/free-brands-svg-icons';
import { far } from '@fortawesome/free-regular-svg-icons';
import HighchartsVue from 'highcharts-vue';
import * as ElementPlusIconsVue from '@element-plus/icons-vue';
import { router } from '@inertiajs/vue3';

library.add(fas,fab,far);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => {
        // console.log(title);
        return `${appName} ${title}`
    },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        window.addEventListener('popstate', () => {
            document.body.style.display = 'none';
            window.location.href = '/login';
        });

        const appInstance = createApp({ render: () => h(App, props) });

        for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
            appInstance.component(key, component)
        }
        // return createApp({ render: () => h(App, props) })
        //     .use(plugin)
        //     .use(ZiggyVue)
        //     .use(ElementPlus, {
        //         locale: es
        //     })
        //     .use(VueTheMask)
        //     .use(HighchartsVue)
        //     // .use(Eye)
        //     // .use(EyeClosed)
        //     .component("font-awesome-icon",FontAwesomeIcon)
        //     .mount(el);
        appInstance
        .use(plugin)
        .use(ZiggyVue)
        .use(ElementPlus, { locale: es })
        .use(VueTheMask)
        .use(HighchartsVue)
        .component("font-awesome-icon", FontAwesomeIcon)
        .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
