module.exports = {
    title: 'Laravel Subby',
    description: 'Laravel Subby is a flexible plans and subscription management system for Laravel.',

    /**
     * Extra tags to be injected to the page HTML `<head>`
     *
     * ref：https://v1.vuepress.vuejs.org/config/#head
     */
    head: [
        ['meta', {name: 'theme-color', content: '#3eaf7c'}],
        ['meta', {name: 'apple-mobile-web-app-capable', content: 'yes'}],
        ['meta', {name: 'apple-mobile-web-app-status-bar-style', content: 'black'}]
    ],
    base: '/laravel-subby/',
    /**
     * Theme configuration, here is the default theme configuration for VuePress.
     *
     * ref：https://v1.vuepress.vuejs.org/theme/default-theme-config.html
     */
    themeConfig: {
        repo: 'https://github.com/bpuig/laravel-subby',
        editLinks: false,
        docsDir: '',
        editLinkText: '',
        lastUpdated: true,
        nav: [
            {text: 'Home', link: '/'},
        ],
        sidebar: {
            '/v3.x/': require('./sidebar/v3.x').sidebar,
            '/v4.x/': require('./sidebar/v4.x').sidebar,
            '/v5.x/': require('./sidebar/v5.x').sidebar,
            '/v6.x/': require('./sidebar/v6.x').sidebar,
            '/v7.x/': require('./sidebar/v7.x').sidebar
        }
    },

    /**
     * Apply plugins，ref：https://v1.vuepress.vuejs.org/zh/plugin/
     */
    plugins: [
        '@vuepress/plugin-back-to-top',
        '@vuepress/plugin-medium-zoom',
    ]
}
