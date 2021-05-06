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
        sidebar: [
            {
                title: 'The package',   // required
                sidebarDepth: 1,
                collapsable: false,
                children: [
                    ['/', 'Introduction'],
                    ['/install/', 'Installation']
                ]
            },
            {
                title: 'Usage',   // required
                sidebarDepth: 2,
                collapsable: false,
                children: [
                    ['/models/', 'Models'],
                    ['/models/plan-model.md', 'Plan Model'],
                    ['/models/plan-feature-model.md', 'Plan Feature Model'],
                    ['/models/plan-subscription-model.md', 'Plan Subscription Model']
                ]
            },
            {
                title: 'Extras',   // required
                sidebarDepth: 3,
                collapsable: false,
                children: [
                    ['/extras/plan-subscription-schedule.md', 'Plan Subscription Schedule'],
                ]
            },
        ]
    },

    /**
     * Apply plugins，ref：https://v1.vuepress.vuejs.org/zh/plugin/
     */
    plugins: [
        '@vuepress/plugin-back-to-top',
        '@vuepress/plugin-medium-zoom',
    ]
}
