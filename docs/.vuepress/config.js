module.exports = {
    title: 'Laravel Subby',
    description: 'Laravel Subby is a flexible plans and subscription management system for Laravel.',
    base: '/test-pages/',
    themeConfig: {
        repo: 'https://github.com/bpuig/laravel-subby',
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
        ]
    }
}
